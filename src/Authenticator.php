<?php
/*
* This file is part of the twitch-api-php package.
*
* (c) Spliced Media <http://www.splicedmedia.com/>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @author Gassan Idriss <ghassani@gmail.com>
*/
namespace Spliced\Twitch\Api;

use GuzzleHttp\Client as HttpClient;

/**
 * Authenticator
 * 
 * Utility class to help getting access tokens for OAuth
 */ 
class Authenticator
{

	const ENDPOINT = 'https://api.twitch.tv/kraken/oauth2/';

	protected $clientId;

	protected $clientSecret;

	protected $redirectUri;

	protected $state;

	protected $scope = array();

	protected $forceVerify = false;

	private $httpClient;

	private $availableScopes = array(
		'user_read',
		'user_blocks_edit',
		'user_blocks_read',
		'user_follows_edit',
		'channel_read',
		'channel_editor',
		'channel_commercial',
		'channel_stream',
		'channel_subscriptions',
		'user_subscriptions',
		'channel_check_subscription',
		'chat_login',
	);

	/**
	* Constructor
	*/
	public function __construct($clientId, $clientSecret, $redirectUri, array $scope = array())
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->redirectUri = $redirectUri;
		$this->scope = $scope;

		$this->httpClient = new HttpClient(array('base_url' => static::ENDPOINT));
	}

	/**
	* getClientId
	*
	* @return string
	*/
	public function getClientId()
	{
		return $this->clientId;
	}

	/**
	* setClientId - Set the client id to authenticate against
	*
	* @param string $clientId
	* @return Authenticator
	*/
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
		return $this;
	}

	/**
	* getClientSecret
	*
	* @return string
	*/
	public function getClientSecret()
	{
		return $this->clientSecret;
	}

	/**
	* setClientSecret - Set the client secret
	*
	* @param string $clientSecret
	* @return Authenticator
	*/
	public function setClientSecret($clientId)
	{
		$this->clientSecret = $clientSecret;
		return $this;
	}

	/**
	* getRedirectUri
	*
	* @return string
	*/
	public function getRedirectUri()
	{
		return $this->redirectUri;
	}

	/**
	* setRedirectUri - Set the redirect uri
	*
	* @param string $redirectUri
	* @return Authenticator
	*/
	public function setRedirectUri($redirectUri)
	{
		$this->redirectUri = $redirectUri;
		return $this;
	}

	/**
	* getState
	*
	* @return string
	*/
	public function getState()
	{
		return $this->state;
	}

	/**
	* setState - Set the state anti-csrf parameter
	*
	* @param string $state
	* @return Authenticator
	*/
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}


	/**
	* setScope - Set the scope, overwriting existing values with the passed value(s)
	*
	* @param string|array $scope 
	* @throws InvalidArgumentException - When a passed scope is invalid
	* @return Authenticator
	*/
	public function setScope($scope)
	{
		if (is_array($scope)) {
			foreach ($scope as $s) {
				$this->validateScope($s);
			}
			$this->scope = $scope;
		} else {
			$this->validateScope($scope);
			$this->scope = array($scope);
		}

		return $this;
	}

	/**
	* getScope - Retrieve the scope stack
	*
	* @return array
	*/
	public function getScope()
	{
		return $this->scope;
	}

	/**
	* addScope - Add a scope into the scope stack
	*
	* @param string|array $scope
	* @throws InvalidArgumentException - When a passed scope is invalid
	* @return Authenticator
	*/
	public function addScope($scope)
	{
		if (is_array($scope)) {
			foreach ($scope as $s) {
				$this->validateScope($s);
				array_push($this->scope, $s);
			}
		} else {
			$this->validateScope($scope);
			array_push($this->scope, $scope);
		}

		return $this;
	}

	/**
	* validateScope - Validate that a passed scope is valid
	*
	* @param string|array $scope
	* @throws InvalidArgumentException - When a passed scope is invalid
	* @return void
	*/
	private function validateScope($scope)
	{
		if (!in_array($scope, $this->availableScopes)) {
			throw new \InvalidArgumentException(sprintf('Scope %s is invalid. Available scopes: %s',
				$s, implode(', ', $this->availableScopes)
			));
		}
	}

	/**
	 * getAuthorizeUrl - The URL to redirect the user to get an authorization code
	 * 
	 * @return string
	 */ 
	public function getAuthorizeUrl()
	{
		return sprintf('%sauthorize?%s', static::ENDPOINT, http_build_query(array(
			'client_id' 	=> $this->getClientId(),
			'redirect_uri'  => $this->getRedirectUri(),
			'state'			=> $this->getState(),
			'scope'			=> implode(' ', $this->scope),
			'response_type' => 'code'
		)));
	}

	/**
	* getToken - Exchange the authorization code for an authorization token
	*
	* @param string $code 
	* @return array
	*/
	public function getToken($code)
	{
		$request = $this->httpClient->createRequest('POST', 'token', array(
				'body' => array(
					'client_id'		=> $this->getClientId(),
					'client_secret'	=> $this->getClientSecret(),
					'state'			=> $this->getState(),
					'grant_type'	=> 'authorization_code',
					'redirect_uri'	=> $this->getRedirectUri(),
					'code'			=> $code
				)
			)					
		);

		return $this->httpClient->send($request)->json();
	}
}