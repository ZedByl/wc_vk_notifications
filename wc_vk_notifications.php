<?php
/*
Plugin Name: WC VK Notifications
Description: Уведомления о новых заказах в ЛС в ВК
Plugin URI: https://github.com/ZedByl/wc_vk_notifications
Version: 1.1.1
Author: ZedByl
License: GPLv3
*/

defined( 'ABSPATH' ) or die('No Access!');
require_once plugin_dir_path(__FILE__) . 'vk.php';
class VkPlugin
{
	public $plugin;
	public $settings = array();
	public $sections = array();
	public $fields = array();

	function __construct() {
		$this->plugin = plugin_basename(__FILE__);
		add_action( 'woocommerce_checkout_order_processed', array($this,'vk_notification'));
    }

	public function vk_notification( $order_id ) {
        $vkID = (int) esc_attr( get_option('vk_id'));
        $vkGroupKey = esc_attr( get_option('vk_group_key'));

        $vkMethods = new VkMethods($vkID, $vkGroupKey);

        // Вся информация о заказе
        $orderData = wc_get_order($order_id);

        // Данные заказа
        $lineCut = '--------------------------------------';
        $currentNote = $orderData->get_customer_note();
        $currentName = $orderData->get_billing_first_name();
        $currentLastName = $orderData->get_billing_last_name();
        $currentCity = $orderData->get_billing_city();
        $currentAddress = $orderData->get_billing_address_1();
        $currentPostCode = $orderData->get_billing_postcode();
        $paymentMethod = $orderData->get_payment_method_title();
        $currentPhone = $orderData->get_billing_phone();
        $shippingToDisplay = $orderData->get_shipping_to_display();
        $paid = $orderData->is_paid() ? 'Оплачен' : 'Неоплачен';
        $code = $orderData->get_coupon_codes()[0] ?: 'Без промокода';

        // Собираем строку для сообщения
        // Инфа которую вбил юзер
        $line = "Заказ: ";$line .= $paid;
        $line .= '<br><br> Имя: ';$line .= $currentName;
        $line .= '<br> Фамилия: ';$line .= $currentLastName;
        $line .= '<br> Город: ';$line .= $currentCity;
        $line .= '<br> Адрес: ';$line .= $currentAddress;
        $line .= '<br> Индекс: ';$line .= $currentPostCode;
        $line .= '<br> Номер телефона: ';$line .= $currentPhone;
        $line .= '<br> Оплата: ';$line .= $paymentMethod;
        $line .= '<br> Доставка: ';$line .= $shippingToDisplay;
        $line .= '<br> Промокод: ';$line .= $code;
        $line .= '<br><br>Пожелания к заказу: ';$line .= $currentNote;

        // Первое разделение сообщения
        $fullOrder = $lineCut;$fullOrder .= '<br>';

        // Собираем инфу обо всем что он купил
        foreach ($orderData->get_items() as $item_key => $item ):
            $fullOrder .= $item["name"];$fullOrder .= ' - ';$fullOrder .= $item["quantity"];$fullOrder .= ' шт.<br>';
            $itemTotal += (int)$item["total"];endforeach;
            $fullOrder .= 'Итог: ';$fullOrder .= $itemTotal;$fullOrder .= ' руб.';$fullOrder .= '<br>';$fullOrder .= $line;$fullOrder .= '<br>';

        // Отделяем сообщение вконце
        $fullOrder .= $lineCut;

        // Отправляем сообщение в вк
        $vkMethods->send($fullOrder);
    }

	public function vkOptionsGroup($input){
		return $input;
	}
	public function vkSection(){
		echo '';
	}
	public function vkSection2(){
		echo '';
	}

	public function vkExample(){
		$value = esc_attr( get_option('vk_id'));
		echo '<input type="text" name="vk_id" value="'. $value .'" placeholder="id">';
	}
	public function vkExample2(){
		$value = esc_attr( get_option('vk_group_key'));
		echo '<input type="text" name="vk_group_key" value="'. $value .'" placeholder="id">';
	}

	public function setSettings(array $settings){
		$this->settings = $settings;
		return $this;
	}

	public function setSections(array $sections){
		$this->sections = $sections;
		return $this;
	}

	public function setFields(array $fields){
		$this->fields = $fields;
		return $this;
	}

	public function setSettings2() {
		$args = array(
			array(
                'option_group'=> 'vk_options_group',
                'option_name'=> 'vk_id',
                'callback' => array($this, 'vkOptionsGroup')
			),
            array(
                'option_group'=> 'vk_options_group',
                'option_name'=> 'vk_group_key',
                'callback' => array($this, 'vkOptionsGroup')
			)

			);
		$this->setSettings($args);
	}

	public function setSections2() {
		$args2 = array(
            array(
                'id' => 'vk_admin_index',
                'title' => '',
                'callback' => array($this, 'vkSection'),
                'page' =>'vk_plugin'
            ),
            array(
                'id' => 'vk_admin_index',
                'title' => '',
                'callback' => array($this, 'vkSection2'),
                'page' =>'vk_plugin'
            ),
		);
		$this->setSections($args2);
	}

	public function setFields2() {
		$args3 = array(
            array(
                'id' => 'vk_id',
                'title' => 'ID страницы куда будут приходить уведомления',
                'callback' => array($this, 'vkExample'),
                'page' =>'vk_plugin',
                'section' =>'vk_admin_index',
                'args' => array(
                    'label_for' => 'vk_id',
                    'class' =>'vks'
                )
            ),
            array(
                'id' => 'vk_group_key',
                'title' => 'Longpoll key вашей группы',
                'callback' => array($this, 'vkExample2'),
                'page' =>'vk_plugin',
                'section' =>'vk_admin_index',
                'args' => array(
                    'label_for' => 'vk_group_key',
                    'class' =>'vks'
                )
            )
		);
		$this->setFields($args3);
	}

	public function registerCustomFields(){
		foreach ( $this->settings as $setting) {
			register_setting($setting["option_group"], $setting["option_name"], $setting["callback"]);
		}
		foreach ( $this->sections as $section) {
			add_settings_section($section["id"], $section["title"], $section["callback"],$section["page"] );
		}
		foreach ( $this->fields as $field) {
			add_settings_field($field["id"], $field["title"], $field["callback"],$field["page"], $field["section"], $field["args"]);
		}
	}

	function register(){
		add_action('admin_menu', array($this, 'add_admin_pages'));
		add_filter("plugin_action_links_$this->plugin", array($this, 'settings_link'));
		add_action( 'woocommerce_checkout_order_processed', array($this,'vk_notification'));
		$this->setSettings2();
		$this->setSections2();
		$this->setFields2();
		add_action('admin_init', array($this, 'registerCustomFields'));
	}
	function settings_link($links){
		$settings_link = '<a href="options-general.php?page=vk_plugin">Settings</a>';
		array_push($links, $settings_link);
		return $links;

	}

	function activate(){
		flush_rewrite_rules();
	}
	public function add_admin_pages() {
		add_menu_page('VK Settings', 'VK','manage_options', 'vk_plugin',array($this, 'admin_index'),'', null);
	}
	public function admin_index() {
		require_once plugin_dir_path(__FILE__) . 'template/admin.php';
	}
	function deactivate(){
		flush_rewrite_rules();
	}
}

if( class_exists('VkPlugin')) {
	$vkplugin = new VkPlugin();
	$vkplugin->register();
}

//activation
register_activation_hook(__FILE__, array($vkplugin, 'activate'));
//deactivation
register_deactivation_hook(__FILE__, array($vkplugin, 'deactivate'));
