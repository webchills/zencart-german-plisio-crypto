<?php
/**
 * package Plisio for Zen Cart German
 * based on Plisio zencart-plugin (https://github.com/Plisio/zencart-plugin)
 * Copyright (c) 2020 Plisio https://plisio.net 
 * Zen Cart German Version - www.zen-cart-pro.at
 * @license MIT License (MIT)
 * @version $Id: plisio.php 2024-02-27 15:23714 webchills $
*/
class plisio extends base {    
  /**
   * name of this module
   *
   * @var string
   */
  public $code;
  /**
   * displayed module title
   *
   * @var string
   */
    public $title;
  /**
   * displayed module description
   *
   * @var string
   */
    public $description;
  /**
   * module status - set based on various config and zone criteria
   *
   * @var boolean
   */
    public $enabled;
  /**
   * Installation 'check' flag
   *
   * @var boolean
   */
  protected $_check;
  /**
   * sort order of display
   *
   * @var int
   */
  public $sort_order = 0;
  /**
   * order status setting for completed orders
   *
   * @var int
   */
    public $order_status;
  /**
   * Variables used in processing transaction request/response values for internal use.
   */
   public $api_key;
    

    function __construct()
    {
        $this->code             = 'plisio';
        $this->title            = MODULE_PAYMENT_PLISIO_TEXT_TITLE;
        $this->description      = MODULE_PAYMENT_PLISIO_TEXT_DESCRIPTION;
        $this->api_key          = defined('MODULE_PAYMENT_PLISIO_API_KEY') ? MODULE_PAYMENT_PLISIO_API_KEY : null;         
        $this->sort_order = defined('MODULE_PAYMENT_PLISIO_SORT_ORDER') ? MODULE_PAYMENT_PLISIO_SORT_ORDER : null;        
        $this->enabled = (defined('MODULE_PAYMENT_PLISIO_STATUS') && MODULE_PAYMENT_PLISIO_STATUS == 'True');  
      if (null === $this->sort_order) return false;
    }

    function javascript_validation()
    {
        return false;
    }

    function selection()
    {
        return array('id' => $this->code, 'module' => $this->title);
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        return false;
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        return false;
    }

    function after_process()
    {
        require_once(dirname(__FILE__) . "/Plisio/version.php");
        require_once(dirname(__FILE__) . "/Plisio/Lib/PlisioClient.php");
        global $insert_id, $order, $messageStack;

        $info = $order->info;

        $callback = zen_href_link('plisio_callback.php', $parameters='', $connection='NONSSL', $add_session_id=true, $search_engine_safe=true, $static=true );

        $client = new PlisioClient\PlisioClient(MODULE_PAYMENT_PLISIO_API_KEY);
        $params = array(
            'order_name' => 'Order #' . $insert_id,
            'order_number' => $insert_id,
            'source_amount' => number_format($info['total'], 2, '.', ''),
            'source_currency' => $info['currency'],
            'callback_url' => $callback,
            'cancel_url' => zen_href_link(FILENAME_CHECKOUT_PAYMENT),
            'success_url' => zen_href_link(FILENAME_CHECKOUT_SUCCESS),
            'email' => $order->customer['email_address'],
            'plugin' => 'ZenCart',
            'version' => PLISIO_ZENCART_EXTENSION_VERSION
        );

        $response = $client->createTransaction($params);
        if ($response && $response['status'] !== 'error' && !empty($response['data'])) {
            $_SESSION['cart']->reset(true);
            zen_redirect($response['data']['invoice_url']);
        } else {
            $messageStack->add_session('checkout_payment', implode(',', json_decode($response['data']['message'], true)), 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT));
            return 'failed';
        }
    }

    function get_error()
    {
        global $HTTP_GET_VARS;
        return array('title' => MODULE_PAYMENT_PLISIO_ORDER_ERROR_TITLE,
            'error' => $HTTP_GET_VARS['error']);
    }

    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query  = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PLISIO_STATUS'");
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    function install()
    {
        global $db, $messageStack;
        if (defined('MODULE_PAYMENT_PLISIO_STATUS')) {
            $messageStack->add_session('Plisio module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=plisio', 'NONSSL'));
            return 'failed';
        }
        $status_query = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);

        $status_id = $status_query->fields['status_id']+1;
        $status_id_paid = $status_id;
        $status_id_pending = $status_id + 1;
        $status_id_expired = $status_id + 2;
        $status_id_cancelled = $status_id + 3;

        $languages = zen_get_languages();

        foreach ($languages as $lang) {
            $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id_paid . "', '" . $lang['id'] . "', 'Plisio [Bezahlt]')");
            $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id_pending . "', '" . $lang['id'] . "', 'Plisio [Wartet]')");
            $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id_expired . "', '" . $lang['id'] . "', 'Plisio [Abgelaufen]')");
            $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id_cancelled . "', '" . $lang['id'] . "', 'Plisio [Abgebrochen]')");
        }

        $flags_query = $db->Execute("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if ($flags_query->RecordCount() == 1) {
            $db->Execute("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id_paid . "'");
            $db->Execute("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id_pending . "'");
            $db->Execute("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id_expired . "'");
            $db->Execute("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id_cancelled . "'");
        }

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Plisio Module', 'MODULE_PAYMENT_PLISIO_STATUS', 'False', 'Enable Plisio Payment Gateway plugin?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Plisio API Key', 'MODULE_PAYMENT_PLISIO_API_KEY', '0', 'Your Plisio API Key', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PLISIO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '8', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Plisio Order Status when order is pending', 'MODULE_PAYMENT_PLISIO_PENDING_STATUS_ID', '" . $status_id_pending .  "', 'Status in your store when order is pending.<br />(\'Plisio [Pending]\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Plisio Order Status when order is cancelled', 'MODULE_PAYMENT_PLISIO_CANCELLED_STATUS_ID', '" . $status_id_cancelled .  "', 'Status in your store when order is cancelled.<br />(\'Plisio [Cancelled]\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Plisio Order Status when order is expired', 'MODULE_PAYMENT_PLISIO_EXPIRED_STATUS_ID', '" . $status_id_expired .  "', 'Status in your store when order is expired.<br />(\'Plisio [Expired]\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Plisio Order Status when order is paid', 'MODULE_PAYMENT_PLISIO_PAID_STATUS_ID', '" . $status_id_paid .  "', 'Status in your store when order is paid.<br />(\'Plisio [Paid]\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        // www.zen-cart-pro.at german admin languages_id==43 START
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Zahlung via Plisio anbieten?', 'MODULE_PAYMENT_PLISIO_STATUS', '43', 'Wollen Sie Kryptozahlungen via Plisio aktivieren?', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Plisio API Key', 'MODULE_PAYMENT_PLISIO_API_KEY', '43', 'Tragen Sie hier Ihren Plisio API Key ein', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_PLISIO_SORT_ORDER', '43', 'Anzeigereigenfolge f�r dieses Modul. Der niedrigste Wert wird zuerst angezeigt..', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Bestellstatus f�r wartende Zahlungen', 'MODULE_PAYMENT_PLISIO_PENDING_STATUS_ID', '43', 'Empfohlen: Plisio [Wartet]', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Bestellstatus f�r abgebrochene Zahlungen', 'MODULE_PAYMENT_PLISIO_CANCELLED_STATUS_ID', '43', 'Empfohlen: Plisio [Abgebrochen]', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Bestellstatus f�r abgelaufene Zahlungen', 'MODULE_PAYMENT_PLISIO_EXPIRED_STATUS_ID', '43', 'Empfohlen: Plisio [Abgelaufen]', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Bestellstatus f�r erfolgreich abgeschlossene Zahlungen', 'MODULE_PAYMENT_PLISIO_PAID_STATUS_ID', '43', 'Empfohlen: Plisio [Bezahlt]', now())");
      // www.zen-cart-pro.at german admin languages_id==43  END
    }

    function remove () {
     global $db;
       $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      // www.zen-cart-pro.at german admin languages_id == delete all
      $db->Execute("delete from " . TABLE_CONFIGURATION_LANGUAGE . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");   
    }

    function keys()
    {
        return array(
            'MODULE_PAYMENT_PLISIO_STATUS',
            'MODULE_PAYMENT_PLISIO_API_KEY',
            'MODULE_PAYMENT_PLISIO_SORT_ORDER',
            'MODULE_PAYMENT_PLISIO_PENDING_STATUS_ID',
            'MODULE_PAYMENT_PLISIO_PAID_STATUS_ID',
            'MODULE_PAYMENT_PLISIO_CANCELLED_STATUS_ID',
            'MODULE_PAYMENT_PLISIO_EXPIRED_STATUS_ID',
        );
    }
}