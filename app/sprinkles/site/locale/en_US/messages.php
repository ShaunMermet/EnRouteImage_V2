<?php

/**
 * en_US
 *
 * US English message token translations for the 'site' sprinkle.
 *
 * @package 
 * @link 
 * @author 
 */

return [
    
    "LOCALE" => [
        "SAVE" => "en_US",
    ],

    "ADMIN" => [
        "PANEL" => "Admin panel"
    ],
    "HOME"  => "Home",
    "LOGOUT" => "Logout",
    "PORTAL" => [
        "WELCOME" => "Welcome !",
        "HEADER1" => [
            "@TRANSLATION" =>   "Tutorial - How to start labeling images.",
            "CONTENT" => "First thing to know, you will find a demo of the labelling tool during this tutorial :)"
        ],
        "STEP1" => [
            "@TRANSLATION" =>   "Step 1 - Get the images !",
            "CONTENT"      => "Normally a set of images should have already been requested by the labeling tool and all you have to do is annotate the image. If it's not the case click on"
        ],
        "STEP2" => [
            "@TRANSLATION" =>   "Step 2 - Select an object type",
            "CONTENT1" => "On the left of the labeling tool you will find a dropdown",
            "CONTENT2" => "with a list of tags that image can be annotated with. Select a category before drawing on the image !"
        ],
        "STEP3" => [
            "@TRANSLATION" =>   "Step 3 - Draw on the image",
            "CONTENT1" => "Click (or touch) on the image to start drawing the bounding box, release to end the selection, and that's it you have successfully annotated an image ! You can now go to the next image by clicking on"
        ],
        "NOTES" => [
            "@TRANSLATION" =>   "Additionnal notes",
            "CONTENT1" => "On the same image multiple bounding boxes of multiple tags can be drawn.",
            "CONTENT2" => "Don't worry about not finishing a set of images, just leave it there, you can come back any time to tag another set :) Images that you have already annotated will be saved.",
            "CONTENT3a" => "If you are not satisfied with your drawing, just click on",
            "CONTENT3b" => "to change mode and then click on the unwanted tag, it will delete it. Don't forget to click on",
            "CONTENT3c" => "to start drawing again !",
        ],
        "DEMO" => [
            "@TRANSLATION" =>   "Demo",
        ],
        "HEADER2" => [
            "@TRANSLATION" =>   "What Next ?",
            "CONTENT1" => "Validation process",
            "CONTENT2" => "The images that you have submitted will be validated by human hands (well, more by eyes !) and the annotation will be definitelly saved.",
            "CONTENT3" => "Sign up !",
            "CONTENT4" => "Sign up to get access to more functionnalities !"
        ],
    ],
    "BUTTON"              => [
        "MORE_IMAGE" => "More image",
        "NEXT_IMAGE" => "Next image",
        "DELETE" => "Delete",
        "DRAW" => "Draw",
        "SIGN_UP" => "Sign up",
        "RESET" => "Reset",
    ],
    "FEEDBACK"              => [
        "NO_IMAGE" => "No image",
    ],
    "LEGEND"              => [
        "LABEL" =>[
            "NBR_BBOX" => "Bbox : ",
        ],
        "SEG" =>[
            "NBR_AREA" => "Areas : ",
        ],
        "EDIT" => "Edit",
        "EDIT_NBR_IMAGE" => "Nbr images : ",
        
    ],
    "LABEL"              => [
        "@TRANSLATION" => "Label",
    ],
    "VALIDATE"           => "Validate",
    
    "LABEL_description"  => "Choose a category and start to tag images !",

    "ACCOUNT" => [

        "SETTINGS" => [
            "STATS" => [
                "@TRANSLATION"     => "Account statistics",
                "SUBMITTED"        => "Number of images waiting for validation",
                "REJECTED"         => "Number of images rejected",
                "VALIDATED"        => "Number of images validated",
                "SUCCESS_RATE"     =>"Success rate
>90% is good
>75% is medium
<75% is not good",
            ],
            "BBOX_STATS"     =>"Bbox statistics",
            "SEG_STATS"     =>"Segmentation statistics"
        ]
    ],


    "GALLERY"               =>"Gallery",
    "UPLOAD" =>[
        "@TRANSLATION" => "Upload",
        "TITLE" => [
        "EXPORT"        =>"Export",
        "CATEGORY"      =>"Categories editor",
        "UPLOAD"        =>"Images uploader",
        ]
    ],
    "BB_UPLOAD"               =>"Bbox",
    "SEG_UPLOAD"               =>"Segmentation",
    "BB_LABEL"               =>"Bbox",
    "BB_LABEL_FULL"               =>"Bounding box",
    "BB_VALIDATE"               =>"BboxValidation",
    "SEG_LABEL"               =>"Segmentation",
    "SEG_VALIDATE"               =>"SegValidation",
    "SEG_LABEL_description" =>"Choose a category and start to tag images !",
    "PASSWORD" => [
        "BETWEEN"   => "Password between {{min}}-{{max}} characters"
    ],
    "GROUP" => [
        "BB_CPRS_RATE"        => "Bbox compression rate",
        "BB_CPRS_RATE_INFO" => "(0-100) 0 is low size, 100 is high quality",
        "SEG_CPRS_RATE"            => "Segmentation compression rate",
        "SEG_CPRS_RATE_INFO"    => "(0-100) 0 is low size, 100 is high quality"
    ],
    "ERROR" => [
        "IMG_OUTDATED"        => "Sorry the image is outdated, you will be forward to the next one.",
    ],
    "COMBOTITLE" => [
        "CATEGORY"        => "Image category",
        "GROUP"        => "Image group",
    ],
    "COMBOPLACEHOLDER" => [
        "NONE"        => "None",
    ]
];