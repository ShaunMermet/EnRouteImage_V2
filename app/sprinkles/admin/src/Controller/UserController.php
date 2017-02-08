<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Controller;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Account\Model\Group;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Util\Password;
use UserFrosting\Sprinkle\Admin\Sprunje\ActivitySprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\RoleSprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\UserSprunje;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for user-related requests, including listing users, CRUD for users, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/navigating/#structure
 */
class UserController extends SimpleController
{
    /**
     * Processes the request to create a new user (from the admin controls).
     *
     * Processes the request from the user creation form, checking that:
     * 1. The username and email are not already in use;
     * 2. The logged-in user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication.
     * Request type: POST
     * @see formUserCreate
     */
    public function create($request, $response, $args)
    {
        // Get POST parameters: user_name, first_name, last_name, email, locale, (group)
        $params = $request->getParsedBody();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_user')) {
            throw new ForbiddenException();
        }

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://user/create.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if username or email already exists
        if ($classMapper->staticMethod('user', 'where', 'user_name', $data['user_name'])->first()) {
            $ms->addMessageTranslated('danger', 'USERNAME.IN_USE', $data);
            $error = true;
        }

        if ($classMapper->staticMethod('user', 'where', 'email', $data['email'])->first()) {
            $ms->addMessageTranslated('danger', 'EMAIL.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // If currentUser does not have permission to set the group, but they try to set it to something other than their own group,
        // throw an exception.
        if (!$authorizer->checkAccess($currentUser, 'create_user_field', [
            'fields' => ['group']
        ])) {
            if (isset($data['group_id']) && $data['group_id'] != $currentUser->group_id) {
                throw new ForbiddenException();
            }
        }

        // In any case, set the group id if not otherwise set
        if (!isset($data['group_id'])) {
            $data['group_id'] = $currentUser->group_id;
        }

        $data['flag_verified'] = 1;
        // Set password as empty on initial creation.  We will then send email so new user can set it themselves via a verification token
        $data['password'] = '';

        // All checks passed!  log events/activities, create user, and send verification email (if required)
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction( function() use ($classMapper, $data, $ms, $config, $currentUser) {
            // Create the user
            $user = $classMapper->createInstance('user', $data);

            // Store new user to database
            $user->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} created a new account for {$user->user_name}.", [
                'type' => 'account_create',
                'user_id' => $currentUser->id
            ]);

            // Load default roles
            $defaultRoleSlugs = $classMapper->staticMethod('role', 'getDefaultSlugs');
            $defaultRoles = $classMapper->staticMethod('role', 'whereIn', 'slug', $defaultRoleSlugs)->get();
            $defaultRoleIds = $defaultRoles->pluck('id')->all();

            // Attach default roles
            $user->roles()->attach($defaultRoleIds);

            // Try to generate a new password request
            $passwordRequest = $this->ci->repoPasswordReset->create($user, $config['password_reset.timeouts.create']);

            // Create and send welcome email with password set link
            $message = new TwigMailMessage($this->ci->view, 'mail/password-create.html.twig');

            $message->from($config['address_book.admin'])
                    ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                    ->addParams([
                        'user' => $user,
                        'create_password_expiration' => $config['password_reset.timeouts.create'] / 3600 . ' hours',
                        'token' => $passwordRequest->getToken()
                    ]);

            $this->ci->mailer->send($message);

            $ms->addMessageTranslated('success', 'ACCOUNT_CREATION_COMPLETE', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Processes the request to send a user a password reset email.
     *
     * Processes the request from the user update form, checking that:
     * 1. The target user's new email address, if specified, is not already in use;
     * 2. The logged-in user has the necessary permissions to update the posted field(s);
     * 3. We're not trying to disable the master account;
     * 4. The submitted data is valid.
     * This route requires authentication.
     * Request type: POST
     */
    public function createPasswordReset($request, $response, $args)
    {
        // Get the username from the URL
        $user = $this->getUserFromParams($args);

        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit "password" for this user
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['password']
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction( function() use ($user, $config) {

            // Create a password reset and shoot off an email
            $passwordReset = $this->ci->repoPasswordReset->create($user, $config['password_reset.timeouts.reset']);

            // Create and send welcome email with password set link
            $message = new TwigMailMessage($this->ci->view, 'mail/password-reset.html.twig');

            $message->from($config['address_book.admin'])
                    ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                    ->addParams([
                        'user' => $user,
                        'token' => $passwordReset->getToken(),
                        'request_date' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

            $this->ci->mailer->send($message);
        });

        $ms->addMessageTranslated("success", "PASSWORD.FORGET.REQUEST_SENT", [
            'email' => $user->email
        ]);
        return $response->withStatus(200);
    }

    /**
     * Processes the request to delete an existing user.
     *
     * Deletes the specified user, removing any existing associations.
     * Before doing so, checks that:
     * 1. You are not trying to delete the master account;
     * 2. You have permission to delete the target user's account.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: DELETE
     */
    public function delete($request, $response, $args)
    {
        $user = $this->getUserFromParams($args);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_user', [
            'user' => $user
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Check that we are not deleting the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($user->id == $config['reserved_user_ids.master']) {
            $e = new BadRequestException();
            $e->addUserMessage('DELETE_MASTER');
            throw $e;
        }

        $userName = $user->user_name;

        $user->delete();
        unset($user);

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'DELETION_SUCCESSFUL', [
            'user_name' => $userName
        ]);

        return $response->withStatus(200);
    }

    /**
     * Returns activity history for a single user.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getActivities($request, $response, $args)
    {
        $user = $this->getUserFromParams($args);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'view_user_field', [
            'user' => $user,
            'property' => 'activities'
        ])) {
            throw new ForbiddenException();
        }

        $sprunje = new ActivitySprunje($classMapper, $params);

        $sprunje->extendQuery(function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        });

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Returns info for a single user.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getInfo($request, $response, $args)
    {
        $user = $this->getUserFromParams($args);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Join user's most recent activity
        $user = $classMapper->createInstance('user')
                            ->where('user_name', $user->user_name)
                            ->joinLastActivity()
                            ->with('lastActivity', 'group')
                            ->first();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_user', [
            'user' => $user
        ])) {
            throw new ForbiddenException();
        }

        // Exclude password from result set
        unset($user->password);

        $result = $user->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns a list of Users
     *
     * Generates a list of users, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getList($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new UserSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders the modal form to confirm user deletion.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalConfirmDelete($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $user = $this->getUserFromParams($params);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_user', [
            'user' => $user
        ])) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'components/modals/confirm-delete-user.html.twig', [
            'user' => $user,
            'form' => [
                'action' => "api/users/u/{$user->user_name}",
            ]
        ]);
    }

    /**
     * Renders the modal form for creating a new user.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * If the currently logged-in user has permission to modify user group membership, then the group toggle will be displayed.
     * Otherwise, the user will be added to the default group and receive the default roles automatically.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalCreate($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        $translator = $this->ci->translator;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_user')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var Config $config */
        $config = $this->ci->config;

        // Determine form fields to hide/disable
        // TODO: come back to this when we finish implementing theming
        $fields = [
            'hidden' => ['theme'],
            'disabled' => []
        ];

        // Get a list of all locales
        $locales = $config['site.locales.available'];

        // Determine if currentUser has permission to modify the group.  If so, show the 'group' dropdown.
        // Otherwise, set to the currentUser's group and disable the dropdown.
        if ($authorizer->checkAccess($currentUser, 'create_user_field', [
            'fields' => ['group']
        ])) {
            // Get a list of all groups
            $groups = $classMapper->staticMethod('group', 'all');
        } else {
            // Get the current user's group
            $groups = $classMapper->staticMethod('group', 'where', 'id', $currentUser->group_id);
            $fields['disabled'][] = 'group';
        }

        // Create a dummy user to prepopulate fields
        $data = [
            'group_id' => $currentUser->group_id,
            'locale'   => $config['site.registration.user_defaults.locale'],
            'theme'    => ''
        ];

        $user = $classMapper->createInstance('user', $data);

        // Load validation rules
        $schema = new RequestSchema('schema://user/create.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/user.html.twig', [
            'user' => $user,
            'groups' => $groups,
            'locales' => $locales,
            'form' => [
                'action' => 'api/users',
                'method' => 'POST',
                'fields' => $fields,
                'submit_text' => $translator->translate("CREATE")
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form for editing an existing user.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEdit($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $user = $this->getUserFromParams($params);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get the user to edit
        $user = $classMapper->staticMethod('user', 'where', 'user_name', $user->user_name)
            ->with('group')
            ->first();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit basic fields "name", "email", "locale" for this user
        $fieldNames = ['name', 'email', 'locale'];
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => $fieldNames
        ])) {
            throw new ForbiddenException();
        }

        // Get a list of all groups
        $groups = $classMapper->staticMethod('group', 'all');

        /** @var Config $config */
        $config = $this->ci->config;

        // Get a list of all locales
        $locales = $config['site.locales.available'];

        // Generate form
        $fields = [
            'hidden' => ['theme'],
            'disabled' => ['user_name']
        ];

        // Disable group field if currentUser doesn't have permission to modify group
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['group']
        ])) {
            $fields['disabled'][] = 'group';
        }

        // Load validation rules
        $schema = new RequestSchema('schema://user/edit-info.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/user.html.twig', [
            'user' => $user,
            'groups' => $groups,
            'locales' => $locales,
            'form' => [
                'action' => "api/users/u/{$user->user_name}",
                'method' => 'PUT',
                'fields' => $fields,
                'submit_text' => 'Update user'
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form for editing a user's password.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditPassword($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $user = $this->getUserFromParams($params);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit "password" field for this user
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['password']
        ])) {
            throw new ForbiddenException();
        }

        // Load validation rules
        $schema = new RequestSchema('schema://user/edit-password.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/user-set-password.html.twig', [
            'user' => $user,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form for editing a user's roles.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditRoles($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $user = $this->getUserFromParams($params);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit "roles" field for this user
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['roles']
        ])) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'components/modals/user-manage-roles.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Returns roles associated with a single user.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getRoles($request, $response, $args)
    {
        $user = $this->getUserFromParams($args);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'view_user_field', [
            'user' => $user,
            'property' => 'roles'
        ])) {
            throw new ForbiddenException();
        }

        $sprunje = new RoleSprunje($classMapper, $params);
        $sprunje->extendQuery(function ($query) use ($user) {
            return $query->forUser($user->id);
        });

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders a page displaying a user's information, in read-only mode.
     *
     * This checks that the currently logged-in user has permission to view the requested user's info.
     * It checks each field individually, showing only those that you have permission to view.
     * This will also try to show buttons for activating, disabling/enabling, deleting, and editing the user.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageInfo($request, $response, $args)
    {
        $user = $this->getUserFromParams($args);

        // If the user no longer exists, forward to main user listing page
        if (!$user) {
            $usersPage = $this->ci->router->pathFor('uri_users');
            return $response->withRedirect($usersPage, 404);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_user', [
                'user' => $user
            ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Get a list of all locales
        $locales = $config['site.locales.available'];

        // Determine fields that currentUser is authorized to view
        $fieldNames = ['name', 'email', 'locale'];

        // Generate form
        $fields = [
            // Always hide these
            'hidden' => ['user_name', 'group', 'theme'],
            'disabled' => []
        ];

        // Determine which fields should be hidden entirely
        foreach ($fieldNames as $field) {
            if ($authorizer->checkAccess($currentUser, 'view_user_field', [
                'user' => $user,
                'property' => $field
            ])) {
                $fields['disabled'][] = $field;
            } else {
                $fields['hidden'][] = $field;
            }
        }

        // Determine buttons to display
        $editButtons = [
            'hidden' => []
        ];

        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['name', 'email', 'locale']
        ])) {
            $editButtons['hidden'][] = 'edit';
        }

        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['flag_enabled']
        ])) {
            $editButtons['hidden'][] = 'enable';
        }

        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['flag_verified']
        ])) {
            $editButtons['hidden'][] = 'activate';
        }

        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['password']
        ])) {
            $editButtons['hidden'][] = 'password';
        }

        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => ['roles']
        ])) {
            $editButtons['hidden'][] = 'roles';
        }

        if (!$authorizer->checkAccess($currentUser, 'delete_user', [
            'user' => $user
        ])) {
            $editButtons['hidden'][] = 'delete';
        }

        return $this->ci->view->render($response, 'pages/user.html.twig', [
            'user' => $user,
            'locales' => $locales,
            'form' => [
                'fields' => $fields,
                'edit_buttons' => $editButtons
            ]
        ]);
    }

    /**
     * Renders the user listing page.
     *
     * This page renders a table of users, with dropdown menus for admin actions for each user.
     * Actions typically include: edit user details, activate user, enable/disable user, delete user.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageList($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/users.html.twig');
    }

    /**
     * Processes the request to update an existing user's basic details (first_name, last_name, email, locale, group_id)
     *
     * Processes the request from the user update form, checking that:
     * 1. The target user's new email address, if specified, is not already in use;
     * 2. The logged-in user has the necessary permissions to update the putted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication.
     * Request type: PUT
     */
    public function updateInfo($request, $response, $args)
    {
        // Get the username from the URL
        $user = $this->getUserFromParams($args);

        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Get PUT parameters
        $params = $request->getParsedBody();

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://user/edit-info.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        // Determine targeted fields
        $fieldNames = [];
        foreach ($data as $name => $value) {
            if ($name == 'first_name' || $name == 'last_name') {
                $fieldNames[] = 'name';
            } elseif ($name == 'group_id') {
                $fieldNames[] = 'group';
            } else {
                $fieldNames[] = $name;
            }
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit submitted fields for this user
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => array_values(array_unique($fieldNames))
        ])) {
            throw new ForbiddenException();
        }

        // Only the master account can edit the master account!
        if (
            ($user->id == $config['reserved_user_ids.master']) &&
            ($currentUser->id != $config['reserved_user_ids.master'])
        ) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if email already exists
        if (
            isset($data['email']) &&
            $data['email'] != $user->email &&
            $classMapper->staticMethod('user', 'where', 'email', $data['email'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'EMAIL.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Update the user and generate success messages
        foreach ($data as $name => $value) {
            if ($value != $user->$name){
                $user->$name = $value;
            }
        }

        $user->save();

        $ms->addMessageTranslated('success', 'DETAILS_UPDATED', [
            'user_name' => $user->user_name
        ]);
        return $response->withStatus(200);
    }

    /**
     * Processes the request to update a specific field for an existing user.
     *
     * Supports editing all user fields, including password, enabled/disabled status and verification status.
     * Processes the request from the user update form, checking that:
     * 1. The logged-in user has the necessary permissions to update the putted field(s);
     * 2. We're not trying to disable the master account;
     * 3. The submitted data is valid.
     * This route requires authentication.
     * Request type: PUT
     */
    public function updateField($request, $response, $args)
    {
        // Get the username from the URL
        $user = $this->getUserFromParams($args);

        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        // Get key->value pair from URL and request body
        $fieldName = $args['field'];

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit the specified field for this user
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', [
            'user' => $user,
            'fields' => [$fieldName]
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Only the master account can edit the master account!
        if (
            ($user->id == $config['reserved_user_ids.master']) &&
            ($currentUser->id != $config['reserved_user_ids.master'])
        ) {
            throw new ForbiddenException();
        }

        // Get PUT parameters: value
        $put = $request->getParsedBody();

        if (!isset($put['value'])) {
            throw new BadRequestException();
        }

        // Create and validate key -> value pair
        $params = [
            $fieldName => $put['value']
        ];

        // Load the request schema
        $schema = new RequestSchema('schema://user/edit-field.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and throw exception on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
            $e = new BadRequestException();
            foreach ($validator->errors() as $idx => $field) {
                foreach($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }
            throw $e;
        }

        // Get validated and transformed value
        $fieldValue = $data[$fieldName];

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        if ($fieldName == 'flag_enabled') {
            // Check that we are not disabling the master account
            if (($user->id == $config['reserved_user_ids.master']) &&
                ($fieldValue == '0')
            ) {
                $e = new ForbiddenException();
                $e->addUserMessage('DISABLE_MASTER');
                throw $e;
            } else if (($user->id == $currentUser->id) &&
                ($fieldValue == '0')
            ) {
                $e = new ForbiddenException();
                $e->addUserMessage('You cannot disable your own account!');
                throw $e;
            }
            if ($fieldValue == '1') {
                $ms->addMessageTranslated('success', 'ENABLE_SUCCESSFUL', [
                    'user_name' => $user->user_name
                ]);
            } else {
                $ms->addMessageTranslated('success', 'DISABLE_SUCCESSFUL', [
                    'user_name' => $user->user_name
                ]);
            }
        } else if ($fieldName == 'flag_verified') {
            $ms->addMessageTranslated('success', 'MANUALLY_ACTIVATED', [
                'user_name' => $user->user_name
            ]);
        } else if ($fieldName == 'password') {
            $fieldValue = Password::hash($fieldValue);
            $ms->addMessageTranslated('success', 'DETAILS_UPDATED', [
                'user_name' => $user->user_name
            ]);
        } else {
            $ms->addMessageTranslated('success', 'DETAILS_UPDATED', [
                'user_name' => $user->user_name
            ]);
        }

        if ($fieldName == "roles") {
            $newRoles = collect($fieldValue)->pluck('role_id')->all();
            $user->roles()->sync($newRoles);
        } else {
            $user->$fieldName = $fieldValue;
            $user->save();
        }

        return $response->withStatus(200);
    }

    protected function getUserFromParams($params)
    {
        // Load the request schema
        $schema = new RequestSchema('schema://user/get-by-username.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and throw exception on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
            $e = new BadRequestException();
            foreach ($validator->errors() as $idx => $field) {
                foreach($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }
            throw $e;
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get the user to delete
        $user = $classMapper->staticMethod('user', 'where', 'user_name', $data['user_name'])
            ->first();

        return $user;
    }
}
