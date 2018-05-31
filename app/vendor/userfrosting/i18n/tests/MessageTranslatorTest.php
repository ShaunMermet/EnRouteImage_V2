<?php

namespace UserFrosting\I18n;

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use UserFrosting\I18n\LocalePathBuilder;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;

class MessageTranslatorTest extends TestCase
{
    protected $basePath;

    protected $locator;

    public function setUp()
    {
        $this->basePath = __DIR__ . '/data';
        $this->locator = new UniformResourceLocator($this->basePath);

        // Add them one at a time to simulate how they are added in SprinkleManager
        $this->locator->addPath('locale', '', 'core/locale');
        $this->locator->addPath('locale', '', 'account/locale');
        $this->locator->addPath('locale', '', 'admin/locale');
    }

    public function testTranslateEN()
    {
        // Load the en_US locale files, no user locale
        $builder = new LocalePathBuilder($this->locator, 'locale://');
        $builder->addLocales('en_US');
        $paths = $builder->buildPaths();
        $loader = new ArrayFileLoader($paths);
    
        // Create the $translator object
        $translator = new MessageTranslator($loader->load());

        // Test most basic functionality
        $this->assertEquals($translator->translate('USERNAME'), "Username");

        // Test most the base locale
        $this->assertEquals($translator->translate('BASE_FALLBACK'), "Base fallback");

        // Test @TRANSLATION
        $this->assertEquals($translator->translate('ACCOUNT'), "Account"); // Shortcut for `ACCOUNT.@TRANSLATION`
        $this->assertEquals($translator->translate('ACCOUNT.ALT'), "Profile");

        // Test basic plural functionality
        $this->assertEquals($translator->translate('COLOR', 0), "colors"); //Note plural in english, singular in french !
        $this->assertEquals($translator->translate('COLOR', 1), "color");
        $this->assertEquals($translator->translate('COLOR', 2), "colors");
        $this->assertEquals($translator->translate('COLOR', 3), "colors");

        // Test plural default
        $this->assertEquals($translator->translate('COLOR'), "color");

        // Test basic nested items
        $this->assertEquals($translator->translate('COLOR.BLACK'), "black");
        $this->assertEquals($translator->translate('COLOR.WHITE'), "white");

        // Test placeholders
        $this->assertEquals($translator->translate('MY_CAR_MAKE', ["car_make" => "Toyota"]), "My car is a Toyota");
        $this->assertEquals($translator->translate('MY_CAR_YEAR', ["year" => 2015]), "I bought my car in 2015");

        // Test plural placeholder
        $this->assertEquals($translator->translate('X_CARS', 0), "no cars");
        $this->assertEquals($translator->translate('X_CARS', 1), "a car");
        $this->assertEquals($translator->translate('X_CARS', 2), "2 cars");
        $this->assertEquals($translator->translate('X_CARS', 10), "10 cars");

        // Example of a lang key in a placeholder
        // N.B.: In a real life situation, it's recommended to create a new Top level plural instead
        $this->assertEquals($translator->translate('MY_CARS', ["x_cars" => $translator->translate('X_CARS', 10)]), "I have 10 cars");

        // Test `+CAR` called (top nested name) without "CAR" defined
        $this->assertEquals($translator->translate('CAR'), "car");

        // Test 3 levels nested with "CAR"
        $this->assertEquals($translator->translate('CAR.GAS'), "gas");
        $this->assertEquals($translator->translate('CAR.EV'), "electric");
        $this->assertEquals($translator->translate('CAR.EV.HYBRID'), "hybrid");
        $this->assertEquals($translator->translate('CAR.HYDROGEN'), "hydrogen");

        // Test extra placeholder (`year` not used)
        $this->assertEquals($translator->translate("MY_CAR_MAKE", ["car_make" => "Toyota", "year" => 2014]), "My car is a Toyota");

        // Test missing placeholder (`car_make` nor defined)
        $this->assertEquals($translator->translate("MY_CAR_MAKE"), "My car is a ");

        // Example of a complex translation
        $this->assertEquals($translator->translate('MY_CAR_STRING', [
            "my_car" => $translator->translate('CAR.EV.PLUGIN_HYBRID'),
            "color" => $translator->translate('COLOR.RED')
        ]), "I drive a red plug-in hybrid");

        // Test the special handles all together
        // MY_EV_CARS => {{who}} have {{plural}} {{type}} {{item}}
        //  - who => Hard coded literal in language file
        //  - plural => 3
        //  - type => Will be replaced by the message of "CAR.EV" key
        //  - item => Hard coded language key in languge file
        $this->assertEquals($translator->translate("MY_EV_CARS", [
            "plural" => 3,
            "type" => "&CAR.EV"
        ]), "I have 3 electric cars");

        // Test that we can still access @TRANSLATION
        $this->assertEquals($translator->translate("MY_EV_CARS"), "My electric cars");

         // Test pluralisation with custom plural key
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 0]), "0 hungry cats");
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 1]), "1 hungry cat");
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 2]), "2 hungry cats");
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 5]), "5 hungry cats");

        // Custom key can also be omited in the placeholder if it's the only placeholder even with custom plural key
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", 5), "5 hungry cats");

        // Test missing pluralisation and placeholder (expected fail)
        $this->assertEquals($translator->translate("X_HUNGRY_CATS"), "1 hungry cat");

        // Test basic placeholder remplacement using int as placeholder value (So they don't try to translate "min" and "max")
        // We don't want to end up with "Votre test doit être entre minimum et 200 patates"
        $this->assertEquals($translator->translate("TEST_LIMIT", ["min" => 4, "max" => 200]), "Your test must be between 4 and 200 potatoes.");
    }

    public function testTranslateFR()
    {
        // Load the en_US locale files as base and fr_FR on top
        $builder = new LocalePathBuilder($this->locator, 'locale://', ['en_US', 'fr_FR']);
        $paths = $builder->buildPaths();
        $loader = new ArrayFileLoader($paths);

        // Create the $translator object
        $translator = new MessageTranslator($loader->load());

        // Test most basic functionality
        $this->assertEquals($translator->translate('USERNAME'), "Nom d'utilisateur");

        // Test most the base locale
        $this->assertEquals($translator->translate('BASE_FALLBACK'), "Base fallback"); // This key is not defined in the french language, so the enlgish string will be returned

        // Test @TRANSLATION
        $this->assertEquals($translator->translate('ACCOUNT'), "Compte de l'utilisateur"); // Shortcut for `ACCOUNT.@TRANSLATION`
        $this->assertEquals($translator->translate('ACCOUNT.ALT'), "Profil");

        // Test basic plural functionality
        $this->assertEquals($translator->translate('COLOR', 0), "couleur"); //Note plural in english, singular in french !
        $this->assertEquals($translator->translate('COLOR', 1), "couleur");
        $this->assertEquals($translator->translate('COLOR', 2), "couleurs");
        $this->assertEquals($translator->translate('COLOR', 3), "couleurs");

        // Test plural default
        $this->assertEquals($translator->translate('COLOR'), "couleur");

        // Test basic nested items
        $this->assertEquals($translator->translate('COLOR.BLACK'), "noir");
        $this->assertEquals($translator->translate('COLOR.WHITE'), "blanc");

        // Test placeholders
        $this->assertEquals($translator->translate('MY_CAR_MAKE', ["car_make" => "Toyota"]), "Ma voiture est une Toyota");
        $this->assertEquals($translator->translate('MY_CAR_YEAR', ["year" => 2015]), "J'ai acheté ma voiture en 2015");

        // Test plural placeholder
        $this->assertEquals($translator->translate('X_CARS', 0), "aucune voiture");
        $this->assertEquals($translator->translate('X_CARS', 1), "une voiture");
        $this->assertEquals($translator->translate('X_CARS', 2), "2 voitures");
        $this->assertEquals($translator->translate('X_CARS', 10), "10 voitures");

        // Example of a lang key in a placeholder
        // N.B.: In a real life situation, it's recommended to create a new Top level plural instead
        $this->assertEquals($translator->translate('MY_CARS', ["x_cars" => $translator->translate('X_CARS', 10)]), "J'ai 10 voitures");

        // Test `+CAR` called (top nested name) without "CAR" defined
        $this->assertEquals($translator->translate('CAR'), "voiture");

        // Test 3 levels nested with "CAR"
        $this->assertEquals($translator->translate('CAR.GAS'), "à essence");
        $this->assertEquals($translator->translate('CAR.EV'), "électrique");
        $this->assertEquals($translator->translate('CAR.EV.HYBRID'), "hybride");
        $this->assertEquals($translator->translate('CAR.HYDROGEN'), "à l'hydrogène");

        // Test extra placeholder (`year` not used)
        $this->assertEquals($translator->translate("MY_CAR_MAKE", ["car_make" => "Toyota", "year" => 2014]), "Ma voiture est une Toyota");

        // Test missing placeholder (`car_make` nor defined)
        $this->assertEquals($translator->translate("MY_CAR_MAKE"), "Ma voiture est une ");

        // Example of a complex translation
        $this->assertEquals($translator->translate('MY_CAR_STRING', [
            "my_car" => $translator->translate('CAR.EV.PLUGIN_HYBRID'),
            "color" => $translator->translate('COLOR.RED')
        ]), "Je conduit une hybride branchable de couleur rouge");

        $this->assertEquals($translator->translate("MY_EV_CARS"), "Mes voitures électriques");

        // Test `plural` pluralisation placeholder with other placeholders
        $this->assertEquals($translator->translate("MY_EV_CARS", [
            "plural" => 3,
            "type" => "&CAR.EV"
        ]), "Le chat a 3 voitures électriques");


         // Test pluralisation with custom plural key
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 0]), "0 chat affamé");
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 1]), "1 chat affamé");
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 2]), "2 chats affamés");
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", ["num" => 5]), "5 chats affamés");

        // Custom key can also be omited in the placeholder if it's the only placeholder even with custom plural key
        $this->assertEquals($translator->translate("X_HUNGRY_CATS", 5), "5 chats affamés");

        // Test missing pluralisation and placeholder (expected fail)
        $this->assertEquals($translator->translate("X_HUNGRY_CATS"), "1 chat affamé");

        // Test basic placeholder remplacement using int as placeholder value (So they don't try to translate "min" and "max")
        // We don't want to end up with "Votre test doit être entre minimum et 200 patates"
        $this->assertEquals($translator->translate("TEST_LIMIT", ["min" => 4, "max" => 200]), "Votre test doit être entre 4 et 200 patates.");
    }

    public function testReadme()
    {
        // Load the en_US locale files, no user locale
        $builder = new LocalePathBuilder($this->locator, 'locale://');
        $builder->setLocales('en_US');
        $paths = $builder->buildPaths();
        $loader = new ArrayFileLoader($paths);

        // Create the $translator object
        $translator = new MessageTranslator($loader->load());

        // Test from the README
        $carMake = "Honda";
        $this->assertEquals($translator->translate("COMPLEX_STRING", [
            "child" => "&X_CHILD",
            "nb_child" => 1,
            "adult" => "&X_ADULT",
            "nb_adult" => 0,
            "color" => "&COLOR.WHITE",
            "car" => "&CAR.FULL_MODEL",
            "make" => $carMake,
            "model" => "Civic",
            "year" => 1993
        ]), "There's a child and no adults in the white Honda Civic 1993");

        $this->assertEquals($translator->translate("COMPLEX_STRING2", [
            "nb_child" => 1,
            "nb_adult" => 0,
            "color" => "&COLOR.WHITE",
            "make" => $carMake,
            "model" => "Civic",
            "year" => 1993
        ]), "There's a child and no adults in the white Honda Civic 1993");
    }

    // Test for placeholder applied to `$key` if it doesn't match any languages keys
    public function testWithoutKeys()
    {
        $translator = new MessageTranslator();
        $this->assertEquals($translator->translate("You are {{status}}", ['status' => 'dumb']), "You are dumb");
    }

    public function testTwigFilters()
    {
        // Load the en_US locale files, no user locale
        $builder = new LocalePathBuilder($this->locator, 'locale://', 'en_US');
        $paths = $builder->buildPaths();
        $loader = new ArrayFileLoader($paths);

        // Create the $translator object
        $translator = new MessageTranslator($loader->load());

        //ESCAPE : http://twig.sensiolabs.org/doc/2.x/filters/escape.html
        //RAW : http://twig.sensiolabs.org/doc/2.x/filters/raw.html
        $this->assertEquals($translator->translate("TWIG.ESCAPE", ["foo" => "<strong>bar</strong>"]), "Placeholder should be escaped : &lt;strong&gt;bar&lt;/strong&gt;");
        $this->assertEquals($translator->translate("TWIG.ESCAPE_DEFAULT", ["foo" => "<strong>bar</strong>"]), "Placeholder should be escaped : &lt;strong&gt;bar&lt;/strong&gt;");
        $this->assertEquals($translator->translate("TWIG.ESCAPE_NOT", ["foo" => "<strong>bar</strong>"]), "Placeholder should NOT be escaped : <strong>bar</strong>");

        //DEFAULT: http://twig.sensiolabs.org/doc/2.x/filters/default.html
        $this->assertEquals($translator->translate("TWIG.DEFAULT"), "Using default: bar");
        $this->assertEquals($translator->translate("TWIG.DEFAULT", ["foo" => "cat"]), "Using default: cat");
        $this->assertEquals($translator->translate("TWIG.DEFAULT_NOT"), "Not using default: ");

        //ABS : http://twig.sensiolabs.org/doc/2.x/filters/abs.html
        $this->assertEquals($translator->translate("TWIG.ABS", ["number" => "-5"]), "5");
        $this->assertEquals($translator->translate("TWIG.ABS_NOT", ["number" => "-5"]), "-5");

        //DATE : http://twig.sensiolabs.org/doc/2.x/filters/date.html
        $this->assertEquals($translator->translate("TWIG.DATE", ["when" => "10 September 2000"]), "09/10/2000");

        //FIRST : http://twig.sensiolabs.org/doc/2.x/filters/first.html
        //LAST: http://twig.sensiolabs.org/doc/2.x/filters/last.html
        $this->assertEquals($translator->translate("TWIG.FIRST", ["numbers" => [1, 3, 5]]), "1");
        $this->assertEquals($translator->translate("TWIG.LAST", ["numbers" => [1, 3, 5]]), "5");

        //NUMBER_FORMAT: http://twig.sensiolabs.org/doc/2.x/filters/number_format.html
        $this->assertEquals($translator->translate("TWIG.NUMBER_FORMAT", ["number" => 9800.333]), "9 800.33");

        //LOWER: http://twig.sensiolabs.org/doc/2.x/filters/lower.html
        //UPPER: http://twig.sensiolabs.org/doc/2.x/filters/upper.html
        //CAPITALIZE: http://twig.sensiolabs.org/doc/2.x/filters/capitalize.html
        $this->assertEquals($translator->translate("TWIG.LOWER", ["string" => "WeLcOmE"]), "welcome");
        $this->assertEquals($translator->translate("TWIG.UPPER", ["string" => "WeLcOmE"]), "WELCOME");
        $this->assertEquals($translator->translate("TWIG.CAPITALIZE", ["string" => "WeLcOmE"]), "Welcome");
    }
}
