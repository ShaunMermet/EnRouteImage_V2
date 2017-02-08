<?php

return [
    "@PLURAL_RULE" => 1, //Required to get the right rule. French is 2, english is 1

    "USERNAME" => 'Username',

    "BASE_FALLBACK" => "Base fallback",

    "ACCOUNT" => [
        "@TRANSLATION" => "Account",
        "ALT" => "Profile"
    ],

    // Colors
    "COLOR" => [
        //Substrings
        "BLACK" => "black",
        "RED" => "red",
        "WHITE" => "white",

        //Plurals
        0 => "colors",
        1 => "color",
        2 => "colors"
    ],

    // Cars
    "CAR" => [
        //Plurals
        1 => "car",
        2 => "cars",

        //Substrings
        "GAS" => "gas",
        "EV" => [
            "@TRANSLATION" => "electric",

            //We don't need those in english. We can use @TRANSLATION instead
            //1 => "electric",
            //2 => "electric",

            //Sub-Substring
            "FULL" => "full electric",
            "HYBRID" => "hybrid",
            "PLUGIN_HYBRID" => "plug-in hybrid"
        ],
        "HYDROGEN" => "hydrogen"
    ],
    "X_CARS" => [
        0 => "no cars",
        1 => "a car",
        2 => "{{plural}} cars"
    ],

    // Placeholder strings
    "MY_CAR_STRING" => "I drive a {{color}} {{my_car}}",
    "MY_CAR_MAKE" => "My car is a {{car_make}}",
    "MY_CAR_YEAR" => "I bought my car in {{year}}",
    "MY_CARS" => "I have {{x_cars}}",

    // Plural with placeholder
    "MY_EV_CARS" => [
        "@TRANSLATION" => "My electric cars",
        1 => "I have a {{type}} {{&CAR}}",
        2 => "I have {{plural}} {{type}} {{&CAR}}"
    ],

    // Custom plural key with no "zero" case.
    // In english, "2" should be used when the plural value is zero. In french, "1" should be used
	"X_HUNGRY_CATS" => [
		"@PLURAL" => 'num',
		1 => "{{num}} hungry cat",
		2 => "{{num}} hungry cats",
	],

	// Min/max placeholder where the
	"TEST_LIMIT" => "Your test must be between {{min}} and {{max}} potatoes.",
	"MIN" => "minimum",
	//"MAX" => "maximum" //Leave disabled for tests
];