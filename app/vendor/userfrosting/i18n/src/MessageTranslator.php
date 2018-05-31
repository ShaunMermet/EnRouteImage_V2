<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/i18n
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */
namespace UserFrosting\I18n;

use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Support\Repository\Repository;

/**
 * MessageTranslator Class
 *
 * Translate message ids to a message in a specified language.
 *
 * @author    Louis Charette
 * @author    Alexander Weissman (https://alexanderweissman.com)
 */
class MessageTranslator extends Repository
{
    /**
     * @var Twig_Environment A Twig environment used to replace placeholders.
     */
    protected $twig;

    /**
     * @var string The default key that contains the pluralization code.
     */
    protected $defaultPluralKey = 'plural';

    /**
     * Create the translator.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;

        $loader = new \Twig_Loader_Filesystem();
        $this->twig = new \Twig_Environment($loader);
    }

    /**
     * Translate the given message id into the currently configured language, substituting any placeholders that appear in the translated string.
     *
     * Return the $messageKey if not match is found
     * @param string $messageKey The id of the message id to translate. can use dot notation for array
     * @param array|int $placeholders[optional] An optional hash of placeholder names => placeholder values to substitute.
     * @return string The translated message.
     */
    public function translate($messageKey, $placeholders = [])
    {
        // If we didn't find a match, we simply apply the placeholders to $messageKey
        if (!$this->has($messageKey)) {
            $message = $messageKey;
        } else {
            // Get the message
            $message = $this->get($messageKey);

            /* If the message is an array, we have to go deeper because an array can countain some special handles:
                - @TRANSLATION
                - @REPLACE
                - @TRANSLATE
                - @PLURAL
            */
            if (is_array($message)) {
                // Is the message array countain any plural rules (keys that are int)
                if (!empty(array_filter(array_keys($message), 'is_int'))) {

                    // Now we can handle plurals using the @PLURAL to define the plural key. If it's not defined, we use the default one
                    $pluralKey = (isset($message['@PLURAL'])) ? $message['@PLURAL'] : $this->defaultPluralKey;

                    // We try get the plural value and default to `1` if none is found
                    // We also allow for a shortcut using the second argument as a numeric value for simple strings.
                    $pluralValue = (isset($placeholders[$pluralKey]) ? (int) $placeholders[$pluralKey] : (!is_array($placeholders) && is_numeric($placeholders) ? $placeholders : null));

                    // Stop for a sec... We don't have a plural value, but before defaut to 1, we check if there's any @TRANSLATION handle
                    if (is_null($pluralValue) && (!$this->has($messageKey . ".@TRANSLATION") || $this->get($messageKey . ".@TRANSLATION") == null)) {

                        //Default
                        $pluralValue = 1;

                    }

                    // If plural value is still null, we have found our message..!
                    if (is_null($pluralValue)) {
                        $message = $this->get($messageKey . ".@TRANSLATION");
                    } else {

                        // Ok great. Now we need the right plural form.
                        // N.B.: Plurals is based on phpBB and Mozilla work : https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
                        $keyFound = false;

                        // 0 is handled differently. We use it so that "0 users" may be displayed as "No users".
                        if ($pluralValue == 0 && isset($message[0])) {
                            $keyFound = 0;
                        } else {

                            $usePluralForm = $this->getPluralForm($pluralValue);
                            if (isset($message[$usePluralForm])) {
                                // The key we need exists, so we use it.
                                $keyFound = $usePluralForm;
                            } else {
                                // If the key we need doesn't exist, we use the previous one.
                                $numbers = array_keys($message);
                                foreach ($numbers as $num) {
                                    if (is_int($num) && $num > $usePluralForm) {
                                        break;
                                    }
                                    $keyFound = $num;
                                }
                            }
                        }

                        // If no key was found, use the last entry (because it is mostly the plural form).
                        if ($keyFound === false) {
                            $numbers = array_keys($message);
                            $keyFound = end($numbers);
                        }

                        $message = $message[$keyFound];

                        // If we used the shortcut and $placeholders is a numeric value
                        // it must be passed back as an array for replacement in the main $message
                        if (is_numeric($placeholders) || empty($placeholders)) {
                            $placeholders = array($pluralKey => $pluralValue);
                        }
                    }

                // @TRANSLATION => When $messageKey is an array, this key is used. To use this, we can't have a plural value
                } elseif ($this->has($messageKey . ".@TRANSLATION")) {
                    $message = $this->get($messageKey . ".@TRANSLATION");
                // If we don't have plural AND a @TRANSLATION, we can't translate any translation key, so we will simply apply the placeholders to $messageKey
                } else {
                    $message = $messageKey;
                }
            }
        }

        // Ok, now we have a $message and need to replace the placeholders
        // Make sure $placeholders is an array otherwise foreach will fail
        if (!is_array($placeholders)) {
            return $message;
        }

        // Interpolate translatable placeholders values. This allows to
        // pre-translate placeholder which value starts with the `&` caracter
        foreach ($placeholders as $name => $value) {
            //We don't allow nested placeholders. They will return errors on the next lines
            if (is_array($value)) {
                continue;
            }

            // We test if the placeholder value starts the "&" caracter.
            // That means we need to translate that placeholder value
            if (substr($value, 0, 1) === '&') {
                // Remove the current placeholder from the master $placeholder
                // array, otherwise we end up in an infinite loop
                $data = array_diff($placeholders, [$name => $value]);

                // Translate placeholders value and place it in the main $placeholder array
                $placeholders[$name] = $this->translate(ltrim($value, '&'), $data);
            }
        }

        // We check for {{&...}} strings in the resulting message.
        // While the previous loop pre-translated placeholder value, this one
        // pre-translate the message string vars
        // We use some regex magic to detect them !
        $message = preg_replace_callback("/{{&(([^}]+[^a-z]))}}/", function ($matches) use ($placeholders) {
            return $this->translate($matches[1], $placeholders);
        }, $message);

        // Now it's time to replace the remaining placeholder. We use Twig do to this.
        // It's a bit slower, but allows to use the many Twig filters
        // See: http://twig.sensiolabs.org/doc/2.x/
        $template = $this->twig->createTemplate($message);
        $message = $template->render($placeholders);

        // Done !
        return $message;
    }

    /**
    * Determine which plural form we should use.
    * For some languages this is not as simple as for English.
    *
    * @param $number        int|float   The number we want to get the plural case for. Float numbers are floored.
    * @param $forceRule    mixed   False to use the plural rule of the language package
    *                               or an integer to force a certain plural rule
    * @return   int     The plural-case we need to use for the number plural-rule combination
    */
    public function getPluralForm($number, $forceRule = false)
    {
        $number = (int) $number;

        // Default to English rule (1) or the forced one
        $rule = ($forceRule !== false) ? $forceRule : (($this->has('@PLURAL_RULE')) ? $this->get('@PLURAL_RULE') : 1);

        if ($rule > 15 || $rule < 0) {
            throw new OutOfRangeException("The rule number '$rule' must be between 0 and 16.");
        }

        /**
        * The following plural rules are based on a list published by the Mozilla Developer Network & code from phpBB Group
        * https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
        */
        switch ($rule) {
            case 0:
                /**
                * Families: Asian (Chinese, Japanese, Korean, Vietnamese), Persian, Turkic/Altaic (Turkish), Thai, Lao
                * 1 - everything: 0, 1, 2, ...
                */
                return 1;
            case 1:
                /**
                * Families: Germanic (Danish, Dutch, English, Faroese, Frisian, German, Norwegian, Swedish), Finno-Ugric (Estonian, Finnish, Hungarian), Language isolate (Basque), Latin/Greek (Greek), Semitic (Hebrew), Romanic (Italian, Portuguese, Spanish, Catalan)
                * 1 - 1
                * 2 - everything else: 0, 2, 3, ...
                */
                return ($number == 1) ? 1 : 2;
            case 2:
                /**
                * Families: Romanic (French, Brazilian Portuguese)
                * 1 - 0, 1
                * 2 - everything else: 2, 3, ...
                */
                return (($number == 0) || ($number == 1)) ? 1 : 2;
            case 3:
                /**
                * Families: Baltic (Latvian)
                * 1 - 0
                * 2 - ends in 1, not 11: 1, 21, ... 101, 121, ...
                * 3 - everything else: 2, 3, ... 10, 11, 12, ... 20, 22, ...
                */
                return ($number == 0) ? 1 : ((($number % 10 == 1) && ($number % 100 != 11)) ? 2 : 3);
            case 4:
                /**
                * Families: Celtic (Scottish Gaelic)
                * 1 - is 1 or 11: 1, 11
                * 2 - is 2 or 12: 2, 12
                * 3 - others between 3 and 19: 3, 4, ... 10, 13, ... 18, 19
                * 4 - everything else: 0, 20, 21, ...
                */
                return ($number == 1 || $number == 11) ? 1 : (($number == 2 || $number == 12) ? 2 : (($number >= 3 && $number <= 19) ? 3 : 4));
            case 5:
                /**
                * Families: Romanic (Romanian)
                * 1 - 1
                * 2 - is 0 or ends in 01-19: 0, 2, 3, ... 19, 101, 102, ... 119, 201, ...
                * 3 - everything else: 20, 21, ...
                */
                return ($number == 1) ? 1 : ((($number == 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 2 : 3);
            case 6:
                /**
                * Families: Baltic (Lithuanian)
                * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
                * 2 - ends in 0 or ends in 10-20: 0, 10, 11, 12, ... 19, 20, 30, 40, ...
                * 3 - everything else: 2, 3, ... 8, 9, 22, 23, ... 29, 32, 33, ...
                */
                return (($number % 10 == 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 < 2) || (($number % 100 >= 10) && ($number % 100 < 20))) ? 2 : 3);
            case 7:
                /**
                * Families: Slavic (Croatian, Serbian, Russian, Ukrainian)
                * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
                * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ...
                * 3 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 26, ...
                */
                return (($number % 10 == 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 2 : 3);
            case 8:
                /**
                * Families: Slavic (Slovak, Czech)
                * 1 - 1
                * 2 - 2, 3, 4
                * 3 - everything else: 0, 5, 6, 7, ...
                */
                return ($number == 1) ? 1 : ((($number >= 2) && ($number <= 4)) ? 2 : 3);
            case 9:
                /**
                * Families: Slavic (Polish)
                * 1 - 1
                * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ... 104, 122, ...
                * 3 - everything else: 0, 5, 6, ... 11, 12, 13, 14, 15, ... 20, 21, 25, ...
                */
                return ($number == 1) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 2 : 3);
            case 10:
                /**
                * Families: Slavic (Slovenian, Sorbian)
                * 1 - ends in 01: 1, 101, 201, ...
                * 2 - ends in 02: 2, 102, 202, ...
                * 3 - ends in 03-04: 3, 4, 103, 104, 203, 204, ...
                * 4 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, ...
                */
                return ($number % 100 == 1) ? 1 : (($number % 100 == 2) ? 2 : ((($number % 100 == 3) || ($number % 100 == 4)) ? 3 : 4));
            case 11:
                /**
                * Families: Celtic (Irish Gaeilge)
                * 1 - 1
                * 2 - 2
                * 3 - is 3-6: 3, 4, 5, 6
                * 4 - is 7-10: 7, 8, 9, 10
                * 5 - everything else: 0, 11, 12, ...
                */
                return ($number == 1) ? 1 : (($number == 2) ? 2 : (($number >= 3 && $number <= 6) ? 3 : (($number >= 7 && $number <= 10) ? 4 : 5)));
            case 12:
                /**
                * Families: Semitic (Arabic)
                * 1 - 1
                * 2 - 2
                * 3 - ends in 03-10: 3, 4, ... 10, 103, 104, ... 110, 203, 204, ...
                * 4 - ends in 11-99: 11, ... 99, 111, 112, ...
                * 5 - everything else: 100, 101, 102, 200, 201, 202, ...
                * 6 - 0
                */
                return ($number == 1) ? 1 : (($number == 2) ? 2 : ((($number % 100 >= 3) && ($number % 100 <= 10)) ? 3 : ((($number % 100 >= 11) && ($number % 100 <= 99)) ? 4 : (($number != 0) ? 5 : 6))));
            case 13:
                /**
                * Families: Semitic (Maltese)
                * 1 - 1
                * 2 - is 0 or ends in 01-10: 0, 2, 3, ... 9, 10, 101, 102, ...
                * 3 - ends in 11-19: 11, 12, ... 18, 19, 111, 112, ...
                * 4 - everything else: 20, 21, ...
                */
                return ($number == 1) ? 1 : ((($number == 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 2 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 3 : 4));
            case 14:
                /**
                * Families: Slavic (Macedonian)
                * 1 - ends in 1: 1, 11, 21, ...
                * 2 - ends in 2: 2, 12, 22, ...
                * 3 - everything else: 0, 3, 4, ... 10, 13, 14, ... 20, 23, ...
                */
                return ($number % 10 == 1) ? 1 : (($number % 10 == 2) ? 2 : 3);
            case 15:
                /**
                * Families: Icelandic
                * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, 131, ...
                * 2 - everything else: 0, 2, 3, ... 10, 11, 12, ... 20, 22, ...
                */
                return (($number % 10 == 1) && ($number % 100 != 11)) ? 1 : 2;
        }
    }
}
