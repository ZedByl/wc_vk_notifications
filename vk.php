<?php
class VkMethods {
	/**
	 * @var string Версия вк
	 */
	private $version;

	/**
	 * @var string access_token из группы в вк
	 */
	private $token;

	/**
	 * @var int ID юзера которому отправляются сообщения
	 */
	private $vkUserID;

	/**
	 * Указываем необходимые параметры
	 *
	 * @param int $vkUserID ID юзера кому будет приходить сообщение
	 * @param string $token access_token, полученный для бота (как минимум, нужны права доступа messages)
	 */
	public function __construct(int $vkUserID, string $token) {
		$this->vkUserID = $vkUserID;
		$this->token = $token;
		$this->version = '5.95';
	}
	/**
	 * Вызов любого метода API
	 *
	 * @param string $fullOrder Сообщение которое отправляем
	 * @return array
	 */
	public function send(string $fullOrder) {
		if(extension_loaded('curl')) {
			$params = array(
				'random_id' => 0,
				'user_id' => $this->vkUserID,
				'access_token' => $this->token,
				'v' => $this->version,
				'message' => $fullOrder
			);

			$ch = curl_init("https://api.vk.com/method/messages.send");
			curl_setopt_array($ch, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => "VKBot/1.0",
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $params
			));

			$json = curl_exec($ch);
			curl_close($ch);

			return json_decode($json, true);
		}

		return false;
	}
}
?>
