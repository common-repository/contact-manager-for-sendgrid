<?php

namespace ContactManagerForSendGrid;

require_once(__DIR__ . "/../includes/SendGridApiCMFS.php");

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Expectation;
use PHPUnit\Framework\TestCase;
use ContactManagerForSendGrid\SendgridApiCMFS;

if (!function_exists('wp_remote_request')) {
    function wp_remote_request($url, $args) {
        assert(is_string($url), "url should be a string " . print_r($url, true));
        $method = "GET";
        if (isset($args['method'])) {
            $method = $args['method'];
        }

        if ($method == "GET") {
            if (isset($args['body']))
                assert(is_array($args['body']), "body should be array for GET requests $url");
        } else {
            assert(isset($args['body']), "Non-GET requests should provide a request body");
            assert(is_string($args['body']), "Non-GET requests body should be a string");
            assert(!!json_decode($args['body']), "Non-GET requests body should be valid json");
        }
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        return json_encode([
           "result" => array([
               "id" => 1,
               "name" => "test"
           ])
        ]);
    }
}

if (!function_exists('get_option')) {
    function get_option($val, $def) {
        return $def;
    }
}

class SendGridTest extends TestCase
{
    private static $listId = [];
    private static $suppressionId = 19513;

    public function setUp(): void
    {
        SendGridTest::$listId = [SENDGRID_LIST_1, SENDGRID_LIST_2];
    }

    public function testAddToList()
    {
        $sendgrid = new SendgridApiCMFS(SENDGRID_KEY, 1 /*sandbox*/);
        $result = $sendgrid->addToList(SendgridTest::$listId, "test@test.com", "first_name", "last_name", "1231231234");
        self::assertFalse(isset($result->errors));
    }

    public function testSingleSend()
    {
        $sendgrid = new SendgridApiCMFS(SENDGRID_KEY, 1 /*sandbox*/);
        $result = $sendgrid->sendEmail(SendGridTest::$listId, SendGridTest::$suppressionId, "Subject",
            "<html><head></head><body></body></html>");
        self::assertFalse(isset($result->errors));
    }

    public function testGetLists()
    {
        $sendgrid = new SendgridApiCMFS(SENDGRID_KEY, 1 /*sandbox*/);
        $result = $sendgrid->getAllLists();
        self::assertNotEmpty($result);
    }
}
