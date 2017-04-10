<?php

// TODO: Make this work!?!?!?!

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Wampum_Form_Sharpspring Class.
 *
 * @since 1.1.0
 */
class Wampum_Form_Sharpspring {

    protected $options;

    protected $session_id;

    /**
     * Start up
     *
     * @since   1.1.0
     *
     * @return  void
     */
    function __construct() {
        $this->options = get_option( 'wampum_forms_ss' );
        $this->session_id = session_id();
    }

    /**
     * Get custom fields
     *
     * @since  1.0.0
     *
     * @return array
     */
    public function get_fields() {

        $params = array(
            'where' => array(
                'isCustom' => '1',
            )
        );
        $data = array(
            'method' => 'getFields',
            'params' => $params,
            'id'     => $this->session_id,
        );

        $queryString = $this->get_sharpspring_query_string();
        $url = "http://api.sharpspring.com/pubapi/v1/?$queryString";

        // Let's get our stuff!
        $results = $this->get_json_data( $url, $data );
        return $results;
    }

    /**
     * Update custom fields
     *
     * @since  1.0.0
     *
     * @param  array  $fields_array  array of fields to create
     *
     * @return idk
     */
    public function update_fields( $fields_array ) {

        /*****************************
        * Example fields_array
        *

        $fields_array = array(
            array(
                'relationship'  => 'lead',
                'dataType'      => 'text',
                'isCustom'      => '1',
                'systemName'    => 'this_key_is_required_for_sharpspring_to_update',
                'label'         => 'This is the visible label - 'systemName' is created from this field,
                'isAvailableInContactManager' => '1',
                'isEditableInContactManager'  => '1',
                'isAvailableInForms'          => '0',
                'isActive'                    => '1',
            ),
        );

        *
        *
        *******************************/

        $params = array(
           'objects' => $fields_array,
        );
        $data = array(
            'method' => 'updateFields',
            'params' => $params,
            'id'     => $this->session_id,
        );

        $queryString = $this->get_sharpspring_query_string();
        $url = "http://api.sharpspring.com/pubapi/v1/respond/?$queryString";

        // Let's get our stuff!
        $results = $this->get_json_data( $url, $data );
        return $results;
    }

    /**
     * Create custom fields
     *
     * @since  1.0.0
     *
     * @param  array  $fields_array  array of fields to create
     *
     * @return idk
     */
    public function create_fields( $fields_array ) {

        /*****************************
        * Example fields_array
        *

        $fields_array = array(
            array(
                'relationship'  => 'lead',
                'dataType'      => 'text',
                'isCustom'      => '1',
                'label'         => 'This is the visible label - 'systemName' is created from this field,
                'isAvailableInContactManager' => '1',
                'isEditableInContactManager'  => '1',
                'isAvailableInForms'          => '0',
                'isActive'                    => '1',
            ),
        );

        *
        *
        *******************************/

        $params = array(
           'objects' => $fields_array,
        );
        $data = array(
            'method'    => 'createFields',
            'params'    => $params,
            'id'        => $this->session_id,
            'header'    => array(
                'accountID' => BB_Sharpspring()->options->get_account_id(),
                'secretKey' => BB_Sharpspring()->options->get_secret_key(),
            ),
        );

        $queryString = $this->get_sharpspring_query_string();
        $url = "http://api.sharpspring.com/pubapi/v1/respond/?$queryString";

        // Let's get our stuff!
        $results = $this->get_json_data( $url, $data );
        return $results;
    }

    /**
     * Get a sharspring lead by email
     *
     * @since  1.0.0
     *
     * @param  string  $email  email address of lead to return
     *
     * @return JSON    lead data from sharpspring
     */
    public function get_lead_id_by_email( $email ) {
        $response = $this->get_leads( '', '', array( 'emailAddress' => $email ) );
        return isset($response->result->lead[0]->id) && ! empty($response->result->lead[0]->id) ? $response->result->lead[0]->id : '';
    }

    /**
     * Get a sharspring lead by email
     *
     * @since  1.0.0
     *
     * @param  string  $email  email address of lead to return
     *
     * @return JSON    lead data from sharpspring
     */
    public function get_lead_by_email( $email ) {
        return $this->get_leads( '', '', array( 'emailAddress' => $email ) );
    }

    /**
     * Get Sharspring leads
     *
     * @since  1.0.0
     *
     * @param  int  $limit  the max amount of leads to return
     * @param  int  $offset the lead offset
     *
     * @return array, i think
     */
    public function get_leads( $limit = '', $offset = '', $where = array() ) {

        $params = array(
            'where'  => $where,
            'limit'  => $limit,
            'offset' => $offset,
        );
        $data = array(
            'method'    => 'getLeads',
            'params'    => $params,
            'id'        => $this->session_id,
        );

        $queryString = $this->get_sharpspring_query_string();
        $url = "http://api.sharpspring.com/pubapi/v1/?$queryString";

        // Let's get our stuff!
        $results = $this->get_json_data( $url, $data );
        return $results;
    }

    /**
     * Update Sharspring leads
     *
     * @since  1.0.0
     *
     * @param  array  $objects  array of leads
     *
     * @return array, i think
     */
    public function update_leads( $objects ) {

        /*****************************
        * Example objects
        *

        $objects = array(
            array(
                'id'                          => 64386430978, // ID is required
                'firstName'                   => 'Mike',
                'lastName'                    => 'Hemberger',
                'emailAddress'                => mike@bizbudding.com,
                'pid1234_salsag789sag798ag79' => '1', // Set this product to 1 (true) for 'purchased'
            ),
            array(
                'id'                          => 12345678910, // ID is required
                'firstName'                   => 'David',
                'lastName'                    => 'Schmeltzle',
                'emailAddress'                => david@bizbudding.com,
                'pid6789_8asg78sg7899'        => '1', // Set this product to 1 (true) for 'purchased'
            ),
        );

        *
        *
        *******************************/

        $params = array(
            'objects' => $objects,
        );
        $data = array(
            'method'    => 'updateLeads',
            'params'    => $params,
            'id'        => $this->session_id,
        );
        $queryString = $this->get_sharpspring_query_string();
        $url = "http://api.sharpspring.com/pubapi/v1/?$queryString";

        // Let's get our stuff!
        $results = $this->get_json_data( $url, $data );
        return $results;
    }

    /**
     * Create Sharspring leads
     *
     * @since  1.0.0
     *
     * @param  array  $objects  array of leads
     *
     * @return array, i think
     */
    public function create_leads( $objects ) {

        /*****************************
        * Example objects
        *

        $objects = array(
            array(
                'firstName'                   => 'Mike',
                'lastName'                    => 'Hemberger',
                'emailAddress'                => mike@bizbudding.com,
                'pid1234_salsag789sag798ag79' => '1', // Set this product to 1 (true) for 'purchased'
            ),
            array(
                'firstName'                   => 'David',
                'lastName'                    => 'Schmeltzle',
                'emailAddress'                => david@bizbudding.com,
                'pid6789_8asg78sg7899'        => '1', // Set this product to 1 (true) for 'purchased'
            ),
        );

        *
        *
        *******************************/

        $params = array(
            'objects' => $objects,
        );
        $data = array(
            'method'    => 'createLeads',
            'params'    => $params,
            'id'        => $this->session_id,
        );
        $queryString = $this->get_sharpspring_query_string();
        $url = "http://api.sharpspring.com/pubapi/v1/?$queryString";

        // Let's get our stuff!
        $results = $this->get_json_data( $url, $data );
        return $results;
    }

    /**
     * Create the query string sharpspring requires for an API call
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_sharpspring_query_string() {
        return http_build_query(array('accountID' => BB_Sharpspring()->options->get_account_id(), 'secretKey' => BB_Sharpspring()->options->get_secret_key()));
    }

    /**
     * Send and return JSON data and return response
     *
     * @since  1.0.0
     *
     * @param  string  $url   URL to send request, with query args as needed
     * @param  array   $data  and array of data to send
     *
     * @return object
     */
    public function get_json_data( $url, $data ) {

        // Get the account ID & Key
        $account_id = BB_Sharpspring()->options->get_account_id();
        $secret_key = BB_Sharpspring()->options->get_secret_key();

        /**
         * Bail if no account ID or secret key
         * Why waste an API call?
         */
        if ( ! trim($account_id) || ! trim($secret_key) ) {
            return;
        }

        $ch   = curl_init($url);
        $data = json_encode($data);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }

    /**
     * Get an array of field names 'systemName' from Sharpspring API
     *
     * @param  array  $results JSON decoded array of raw results from Sharspring call
     *
     * @return array  an array of item key values
     */
    public function get_system_names($results) {
        return $this->get_sharpspring_data_by_object_item( $results->result->field, 'systemName' );
    }

    /**
     * Get an array of field names 'systemName' from Sharpspring API
     *
     * @param  array  $results  JSON decoded array of raw results from Sharspring call
     * @param  string $item_key the key/item value from each object in the field results
     *
     * @return array  an array of item key values
     */
    public function get_sharpspring_data_by_object_item( $results, $item_key ) {
        return wp_list_pluck( $results, $item_key );
    }

}
