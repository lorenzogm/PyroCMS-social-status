<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Events_Social_status
{
	protected $ci;

	public function __construct()
	{
		$this->ci =& get_instance();

		Events::register('streams_post_insert_entry', array($this, 'post_status'));
	}

	public function post_status($stream)
	{
		if($stream['stream']->stream_slug == 'social_status')
		{
			$this->ci->load->model('social/credential_m');

			$url = $stream['insert_data']['url'];

			// Try and post that shit to facebook!
			if (($credentials = $this->ci->credential_m->get_active_provider('facebook')))
			{
				$params = array(
					'access_token' => $credentials->access_token,
					'name'=> $this->ci->config->config['ion_auth']['site_title'],
					'message'=> html_entity_decode(strip_tags($stream['insert_data']['facebook_message'])),
					'link' => $url,
					);

				log_message('info', 'Post status with Facebook: '.json_encode($params));

				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL => 'https://graph.facebook.com/me/feed',
					CURLOPT_POSTFIELDS => $params,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_VERBOSE => true
					));
				$result = curl_exec($ch);
			}

			// Twitter wants it too... yeah she does!
			if (($credentials = $this->ci->credential_m->get_active_provider('twitter')))
			{
				$this->ci->load->library('twitter', array(
					'consumer_key' => $credentials->client_key,
					'consumer_secret' => $credentials->client_secret,
					'oauth_token' => $credentials->access_token,
					'oauth_token_secret' => $credentials->secret,
					));

				$message = character_limiter(strip_tags($stream['insert_data']['twitter_message']), 130).' '.$url;

				log_message('info', 'Post status with Twitter: '.json_encode(array('status' => $message)));

				$this->ci->twitter->post('statuses/update', array('status' => $message));
			}
		}
	}
}

/* End of file events.php */