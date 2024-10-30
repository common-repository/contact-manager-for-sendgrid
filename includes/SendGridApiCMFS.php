<?php

namespace ContactManagerForSendGrid;

class SendgridApiCMFS
{
    private $apiKey = "";
    private $sandbox = false;

    public function hasKey() {
        return !!$this->apiKey;
    }

    /**
     * @param string $apiKey - if empty gets value from sl-sendgrid-key option
     * @param int|bool $sandbox - if empty gets value from sl-sendgrid-sandbox option
     */
    public function __construct($apiKey = "", $sandbox = 2)
    {
        if ($apiKey)
            $this->apiKey = $apiKey;
        else
            $this->apiKey = get_option("cmfs-sendgrid-key");

        if ($sandbox != 2)
            $this->sandbox = !!$sandbox;
        else
            $this->sandbox = get_option('cmfs-sendgrid-sandbox');

    }

    /**
     * Add contact to specific list
     * @param $listId string|array of list ids
     * @param $email string
     *
     * @return object
     */
    public function addToList($listId, $email, $first_name, $last_name = "", $phone = "")
    {
        if (!is_array($listId))
            $listId = [$listId];
        $data = [
            "list_ids" => $listId,
            "contacts" => [
                [
                    "email" => $email,
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "phone_number" => $phone,
                ]
            ]
        ];

        return $this->sendRequest("/marketing/contacts", "PUT", $data);
    }

    public function getAllUnsubscribeGroups()
    {
        return $this->sendRequest("/asm/groups", "GET", []);
    }

    /**
     * Send email to list with suppression group
     *
     * @param string $listId
     * @param int $suppressionId
     * @param string $subject
     * @param string $message
     */
    public function sendEmail($listId, $suppressionId, $subject, $message)
    {
        $list_ids = (is_array($listId) ? $listId : [$listId]);
        $sender_option = get_option("tw-sendgrid-sender", null);
        if ($sender_option) {
            $sender_option = intval($sender_option);
        }
        $data = [
            "name" => "Single Send From WordPress",
            "send_to" => [
                "list_ids" => $list_ids
            ],
            "email_config" => [
                "subject" => $subject,
                "html_content" => $message,
                "suppression_group_id" => intval($suppressionId),
                "sender_id" => $sender_option
            ]
        ];

        return $this->sendRequest("/marketing/singlesends", "POST", $data);
    }

    /**
     * Get all sendgrid contact lists
     *
     * @return array
     */
    public function getAllLists()
    {
        $result = $this->sendRequest("/marketing/lists?page_size=100", "GET", []);
        $options = [];
        if ($result && isset($result->result)) {
            foreach($result->result as $value) {
                $options[$value->id] = $value->name;
            }
        }
        return $options;
    }

    /**
     * Send request to sendgrid api with given endpoint and data
     *
     * @return object|null|array api request response
     */
    private function sendRequest($url, $method, $data)
    {
        if (!$this->apiKey) {
            error_log("Missing SendGrid API Key. Add key in Settings->Contacts for SendGrid");
            return null;
        }
        if ($this->sandbox) {
            $data['mail_settings'] = [
                'sandbox_mode' => [
                    "enable" => true
                ]
            ];
        }

        $args = array(
            'method' => $method,
            'headers'     => array(
                "Authorization" =>  "Bearer " . $this->apiKey,
                'Content-Type' => 'application/json'
            ),
        );

        if ($method != "GET") {
            $args['body'] = json_encode($data);
            $args['data_format'] = 'body';
        } else {
            $args['body'] = $data;
        }

        $response = wp_remote_request("https://api.sendgrid.com/v3" . $url, $args);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body);
        if (isset($json->errors) && $json->errors) {
            error_log(print_r($response, true));
            return null;
        }

        return $json;
    }
}
