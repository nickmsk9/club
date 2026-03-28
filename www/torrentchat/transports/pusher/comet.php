<?php

/*

CometChat
Copyright (c) 2011 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

class Comet {

    function Comet() {
    }

    function publish($args) {
		if (!$args['channel']) {
            echo('Missing Channel');
            return false;
        }

        $channel = $args['channel'];

		$sql = "insert into cometchat_apehistory (channel,message,sent) values ( '".mysql_real_escape_string($channel). "', '" . mysql_real_escape_string(serialize($args['message'])) . "','".getTimeStamp()."')";
		mysql_query($sql);

		$args['message']['message'] = str_replace("'",'%27',str_replace('"','%22',$args['message']['message']));
		
		global $pusher_app_id;
		global $pusher_key;
		global $pusher_secret;

		$pusher = new Pusher($pusher_key, $pusher_secret, $pusher_app_id, $channel);
		$pusher->trigger($channel,'message',$args['message']);   
	}

    function history($args) {
        if (!$args['channel']) {
            echo('Missing Channel');
            return false;
        }
		
		$response['messages'] = array();
		$limit   = +$args['limit'] ? +$args['limit'] : 10;
		$sql = "select message from cometchat_apehistory where channel = '".mysql_real_escape_string($args['channel'])."' order by id desc limit 0, ".$limit;
		$result = mysql_query($sql);

		while($row = mysql_fetch_array($result)) {
			$response['messages'][] = unserialize($row['message']);
		}

        return $response['messages'];
    }

}


/* 
	Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php

	Contrbutors:
		+ Paul44 (http://github.com/Paul44)
		+ Ben Pickles (http://github.com/benpickles)
		+ Mastercoding (http://www.mastercoding.nl)
*/

class PusherInstance {
	
	private static $instance = null;
	private static $app_id  = '';
	private static $secret  = '';
	private static $api_key = '';
	
	private function __construct() { }
	private function __clone() { }
	
	public static function get_pusher()
	{
		if (self::$instance !== null) return self::$instance;

		self::$instance = new Pusher(
			self::$api_key, 
			self::$secret, 
			self::$app_id
		);

		return self::$instance;
	}
}

class Pusher
{

	private $settings = array ();

	public function __construct( $auth_key, $secret, $app_id, $debug = false, $host = 'http://api.pusherapp.com', $port = '80', $timeout = 30 )
	{

		// Check compatibility, disable for speed improvement
		$this->check_compatibility();

		// Setup defaults
		$this->settings['server']	= $host;
		$this->settings['port']		= $port;
		$this->settings['auth_key']	= $auth_key;
		$this->settings['secret']	= $secret;
		$this->settings['app_id']	= $app_id;
		$this->settings['url']		= '/apps/' . $this->settings['app_id'];
		$this->settings['debug']	= $debug;
		$this->settings['timeout']	= $timeout;

	}

	/**
	* Check if the current PHP setup is sufficient to run this class
	*/
	private function check_compatibility()
	{

		// Check for dependent PHP extensions (JSON, cURL)
		if ( ! extension_loaded( 'curl' ) || ! extension_loaded( 'json' ) )
		{
			die( 'There is missing dependant extensions - please ensure both cURL and JSON modules are installed' );
		}

		# Supports SHA256?
		if ( ! in_array( 'sha256', hash_algos() ) )
		{
			die( 'SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.' );
		}

	}

	public function trigger( $channel, $event, $payload, $socket_id = null, $debug = false )
	{

		# Check if we can initialize a cURL connection
		$ch = curl_init();
		if ( $ch === false )
		{
			die( 'Could not initialise cURL!' );
		}

		# Add channel to URL..
		$s_url = $this->settings['url'] . '/channels/' . $channel . '/events';

		# Build the request
		$signature = "POST\n" . $s_url . "\n";
		$payload_encoded = json_encode( $payload );
		$query = "auth_key=" . $this->settings['auth_key'] . "&auth_timestamp=" . time() . "&auth_version=1.0&body_md5=" . md5( $payload_encoded ) . "&name=" . $event;

		# Socket ID set?
		if ( $socket_id !== null )
		{
			$query .= "&socket_id=" . $socket_id;
		}

		# Create the signed signature...
		$auth_signature = hash_hmac( 'sha256', $signature . $query, $this->settings['secret'], false );
		$signed_query = $query . "&auth_signature=" . $auth_signature;
		$full_url = $this->settings['server'] . ':' . $this->settings['port'] . $s_url . '?' . $signed_query;

		# Set cURL opts and execute request
		curl_setopt( $ch, CURLOPT_URL, $full_url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array ( "Content-Type: application/json" ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload_encoded );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->settings['timeout'] );

		$response = curl_exec( $ch );

		curl_close( $ch );

		if ( $response == "202 ACCEPTED\n" && $debug == false )
		{
			return true;
		}
		elseif ( $debug == true || $this->settings['debug'] == true )
		{
			return $response;
		}
		else
		{
			return false;
		}

	}

	public function socket_auth( $channel, $socket_id, $custom_data = false )
	{

		if($custom_data == true)
		{
			$signature = hash_hmac( 'sha256', $socket_id . ':' . $channel . ':' . $custom_data, $this->settings['secret'], false );
		}
		else
		{
			$signature = hash_hmac( 'sha256', $socket_id . ':' . $channel, $this->settings['secret'], false );
		}

		$signature = array ( 'auth' => $this->settings['auth_key'] . ':' . $signature );
		// add the custom data if it has been supplied
		if($custom_data){
		  $signature['channel_data'] = $custom_data;
		}
		return json_encode( $signature );

	}

	public function presence_auth( $channel, $socket_id, $user_id, $user_info = false )
	{

		$user_data = array( 'user_id' => $user_id );
		if($user_info == true)
		{
			$user_data['user_info'] = $user_info;
		}

		return $this->socket_auth($channel, $socket_id, json_encode($user_data) );
	}


}




?>