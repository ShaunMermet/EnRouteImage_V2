<?php

use PHPUnit\Framework\TestCase;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;

class RequestSchemaTest extends TestCase
{
    protected $basePath;

    protected $contactSchema;
    
    public function setUp()
    {
        $this->basePath = __DIR__ . '/data';

        $this->contactSchema = [
            "message" => [
                "validators" => [
                    "required" => [
                        "message" => "Please enter a message"
                    ]
                ]
            ]
        ];
    }

    public function testReadJsonSchema()
    {
        // Arrange
        $loader = new YamlFileLoader($this->basePath . '/contact.json');
        $schema = new RequestSchemaRepository($loader->load());

        // Act
        $result = $schema->all();

        // Assert
        $this->assertArraySubset($this->contactSchema, $result);
    }

    public function testReadYamlSchema()
    {
        // Arrange
        $loader = new YamlFileLoader($this->basePath . '/contact.yaml');
        $schema = new RequestSchemaRepository($loader->load());

        // Act
        $result = $schema->all();

        // Assert
        $this->assertArraySubset($this->contactSchema, $result);
    }

    public function testSetDefault()
    {
        // Arrange
        $loader = new YamlFileLoader($this->basePath . '/contact.yaml');
        $schema = new RequestSchemaRepository($loader->load());

        // Act
        $schema->setDefault('message', "I require more voles.");
        $result = $schema->all();

        // Assert
        $contactSchema = [
            "message" => [
                "default" => "I require more voles.",
                "validators" => [
                    "required" => [
                        "message" => "Please enter a message"
                    ]
                ]
            ]
        ];
        $this->assertArraySubset($contactSchema, $result);
    }

    public function testAddValidator()
    {
        // Arrange
        $loader = new YamlFileLoader($this->basePath . '/contact.yaml');
        $schema = new RequestSchemaRepository($loader->load());

        // Act
        $schema->addValidator('message', 'length', [
            'max' => 10000,
            'message' => 'Your message is too long!'
        ]);
        $result = $schema->all();

        // Assert
        $contactSchema = [
            "message" => [
                "validators" => [
                    "required" => [
                        "message" => "Please enter a message"
                    ],
                    "length" => [
                        "max" => 10000,
                        "message" => "Your message is too long!"
                    ]
                ]
            ]
        ];
        $this->assertArraySubset($contactSchema, $result);
    }

    public function testRemoveValidator()
    {
        // Arrange
        $schema = new RequestSchemaRepository([
            "message" => [
                "validators" => [
                    "required" => [
                        "message" => "Please enter a message"
                    ],
                    "length" => [
                        "max" => 10000,
                        "message" => "Your message is too long!"
                    ]
                ]
            ]
        ]);

        // Act
        $schema->removeValidator('message', 'required');
        // Check that attempting to remove a rule that doesn't exist, will have no effect
        $schema->removeValidator('wings', 'required');
        $schema->removeValidator('message', 'telephone');

        $result = $schema->all();

        // Assert
        $contactSchema = [
            "message" => [
                "validators" => [
                    "length" => [
                        "max" => 10000,
                        "message" => "Your message is too long!"
                    ]
                ]
            ]
        ];

        $this->assertEquals($contactSchema, $result);
    }

    public function testSetTransformation()
    {
        // Arrange
        $loader = new YamlFileLoader($this->basePath . '/contact.yaml');
        $schema = new RequestSchemaRepository($loader->load());

        // Act
        $schema->setTransformations('message', ['purge', 'owlify']);
        $result = $schema->all();

        // Assert
        $contactSchema = [
            "message" => [
                "validators" => [
                    "required" => [
                        "message" => "Please enter a message"
                    ]
                ],
                "transformations" => [
                    "purge",
                    "owlify"
                ]
            ]
        ];
        $this->assertArraySubset($contactSchema, $result);
    }
}
