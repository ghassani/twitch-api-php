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
use GuzzleHttp\Message\Request;

/**
 * Client - Twich API wrapper client
 * 
 * Convers all currently documented v3 API calls
 * 
 * @see https://github.com/justintv/Twitch-API/blob/master/v3_resources/
 */
class Client
{
	const ENDPOINT = 'https://api.twitch.tv/kraken/';
	
	const ENDPOINT_VERSION = 3;

	const ORDER_DIRECTION_ASC 	= 'asc';

	const ORDER_DIRECTION_DESC 	= 'desc';

	const PERIOD_ALL 			= 'all';

	const PERIOD_WEEK 			= 'week';

	const PERIOD_MONTH 			= 'month';

	const STREAM_TYPE_ALL		= 'all';

	const STREAM_TYPE_PLAYLIST  = 'playlist';

	const STREAM_TYPE_LIVE  	= 'live';

	protected $username;

	protected $accessToken;

	protected $httpClient;

	/**
	 * Constructor
	 * 
	 * @param string $username - The username the client will be working with
	 * @param string $accessToken - The access token from OAuth exchange 
	 */
	public function __construct($username, $accessToken)
	{
		$this->username 	= $username;
		$this->accessToken 	= $accessToken;
		$this->httpClient 	= new HttpClient(array('base_url' => static::ENDPOINT));
	}

	/**
	 * getAccessToken
	 * 
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * setAccessToken
	 * 
	 * @param string $accessToken
	 * 
	 * @return Client
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
		return $this;
	}

	/**
	 * getUsername
	 * 
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * setUsername
	 * 
	 * @param string $username
	 * 
	 * @return Client
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	/**
	 * getSupportedApiVersion
	 * 
	 * @return int
	 */
	public function getSupportedApiVersion()
	{
		return static::ENDPOINT_VERSION;
	}

	/**
	 * getStatus - Retrieve the current API status and authentication status if Access Token is available and valid
	 */ 
	public function getStatus()
	{
		return $this->sendRequest($this->httpClient->createRequest('GET', ''));
	}

	/**
	 * getUserBlockList - Retrieve the currently authenticated users block list
	 * 
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getUserBlockList($limit = 25, $offset = 0)
	{
		return $this->sendRequest($this->httpClient->createRequest('GET', sprintf('users/%s/blocks', $this->getUsername()), array(
			'query' => array(
				'limit' => $limit,
				'offset' => $offset
			)
		)));
	}

	/**
	 * addUserToBlockList - Add a user to the currently authenticated users block list
	 * 
	 * @param string $usernameToBlock
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function addUserToBlockList($usernameToBlock)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('PUT', sprintf('users/%s/blocks/%s', $this->getUsername(), $usernameToBlock))
		);
	}

	/**
	 * removeUserFromBlockList - Remove a user to the currently authenticated users block list
	 * 
	 * @param string $usernameToRemove
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function removeUserFromBlockList($usernameToRemove)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('DELETE', sprintf('users/%s/blocks/%s', $this->getUsername(), $usernameToRemove))
		);
	}

	/**
	 * getChannel - Retrieve a channel object for a specified channel or the currently authenticated users channel
	 * 
	 * @param string $channel - Optionally specify a channel, or use the current authenticated users channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChannel($channel = null)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', $channel ? sprintf('channels/%s', $channel) : 'channel')
		);
	}

	/**
	 * getChannelFollows - Retrieve a list of users who follow a specific channel
	 * 
	 * @param string $channel
	 * @param int $limit
	 * @param int $offset
	 * @param string $cursor
	 * @param string $direction
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChannelFollows($channel, $limit = 25, $offset = 0, $cursor = null, $direction = self::ORDER_DIRECTION_DESC)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('channels/%s/follows', $channel), array(
				'query' => array(
					'limit' 	=> $limit,
					'offset' 	=> $offset,
					'cursor' 	=> $cursor,
					'direction' => $direction
				)
			))
		);
	}

	/**
	 * getChannelEditors - Retrieve a channel editor list
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChannelEditors($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('channels/%s/editors', $channel))
		);
	}

	/**
	 * updateChannel - Update the currently authenticated users channel
	 * 
	 * @param string $channel
	 * @param string $status
	 * @param string $game
	 * @param int $delay
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function updateChannel($channel, $status, $game, $delay = 60)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('PUT', sprintf('channels/%s', $channel), array(
				'query' => array(
					'status' 	=> $status,
					'game' 		=> $game,
					'delay' 	=> $delay,
				)
			))
		);
	}

	/**
	 * resetChannelStreamKey - Reset a channels stream key
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function resetChannelStreamKey($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('DELETE', sprintf('channels/%s/stream_key', $channel))
		);
	}

	/**
	 * startChannelCommercial
	 * 
	 * @param string $channel
	 * @param int $length
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function startChannelCommercial($channel, $length = 30)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('POST', sprintf('channels/%s', $channel), array(
				'body' => array(
					'length' 	=> $length,
				)
			))
		);
	}

	/**
	 * getChannelTeams
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChannelTeams($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('channels/%s/teams', $channel))
		);
	}

	/**
	 * getChat
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChat($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('chat/%s', $channel))
		);
	}

	/**
	 * getChatBadges
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChatBadges($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('chat/%s/badges', $channel))
		);
	}

	/**
	 * getChatEmoticons
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChatEmoticons()
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'chat/emoticons')
		);
	}

	/**
	 * getChatEmoticonsImages
	 * 
	 * @param array emotesets - Optionally restrict to emote sets
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getChatEmoticonsImages(array $emotesets = array())
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'channels/emoticon_images', array(
				'query' => array(
					'emotesets' => implode(',', $emotesets),
				)
			))
		);
	}

	/**
	 * getUserFollows
	 * 
	 * @param string $username
	 * @param int $limit
	 * @param int $direction
	 * @param string $soryBy
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getUserFollows($username, $limit = 25, $offset = 0, $direction = self::ORDER_DIRECTION_DESC, $sortBy = 'created_at')
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('users/%s/follows/channels', $username), array(
				'query' => array(
					'limit' 	=> $limit,
					'offset' 	=> $offset,
					'direction' => $direction,
					'sortby' 	=> $sortBy,
				)
			))
		);
	}

	/**
	 * getUserFollowRelationship
	 * 
	 * @param string $username
	 * @param int $limit
	 * @param int $direction
	 * @param string $soryBy
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function getUserFollowRelationship($username, $channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('users/%s/follows/channels/%s', $username, $channel))
		);
	}

	/**
	 * followChannel
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function followChannel($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('PUT', sprintf('users/%s/follows/channels/%s', $this->getUsername(), $channel))
		);
	}

	/**
	 * followChannel
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	public function unfollowChannel($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('DELETE', sprintf('users/%s/follows/channels/%s', $this->getUsername(), $channel))
		);
	}

	/**
	 * getUserStreamFollows
	 * 
	 * @param int $limit
	 * @param int $offset
	 * @param string $type
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getUserStreamFollows($limit = 25, $offset = 0, $type = self::STREAM_TYPE_ALL)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'streams/followed', array(
				'query' => array(
					'limit' 		=> $limit,
					'offset' 		=> $offset,
					'stream_type' 	=> $type
				)
			))
		);
	}

	/**
	 * getUserVideoFollows
	 * 
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getUserVideoFollows($limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'videos/followed', array(
				'query' => array(
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}	

	/**
	 * getTopGames
	 * 
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getTopGames($limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'games/top', array(
				'query' => array(
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * getIngests
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getIngests()
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'ingests')
		);
	}

	/**
	 * searchChannels
	 * 
	 * @param string $query
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function searchChannels($query, $limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'search/channels', array(
				'query' => array(
					'q' 			=> $query,
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * searchStreams
	 * 
	 * @param string $query
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function searchStreams($query, $limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'search/streams', array(
				'query' => array(
					'q' 			=> $query,
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * searchGames
	 * 
	 * @param string $query
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function searchGames($query, $limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'search/games', array(
				'query' => array(
					'q' 			=> $query,
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * getStream
	 * 
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getStream($channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('streams/%s', $channel))
		);
	}

	/**
	 * getStream
	 * 
	 * @param array $filters - Available filters: game, channel, client_id
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getStreams(array $filters = array(), $limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'streams', array(
				'query' => array(
					'game' 			=> isset($filters['game']) 		  ? $filters['game'] : null,
					'channel' 		=> isset($filters['channel']) 	  ? $filters['channel'] : null,
					'client_id' 	=> isset($filters['client_id'])   ? $filters['client_id'] : null,
					'stream_type' 	=> isset($filters['stream_type']) ? $filters['stream_type'] : null,
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * getFeaturedStreams
	 * 
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getFeaturedStreams($limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'streams/featured', array(
				'query' => array(
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * getStreamsSummary
	 * 
	 * @param string $game - Optonally filter stream summarries by game name
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getStreamsSummary($game = null)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'streams/summary', array(
				'query' => array(
					'game' 		=> $game,
				)
			))
		);
	}

	/**
	 * getChannelSubscribers
	 * 
	 * @param string $channel
	 * @param int $limit
	 * @param int $offset
	 * @param string $direction
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getChannelSubscribers($channel, $limit = 25, $offset = 0, $direction = ORDER_DIRECTION_ASC)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('channels/%s/subscriptions', $channel), array(
				'query' => array(
					'limit' 		=> $limit,
					'offset' 		=> $offset,
					'direction' 	=> $direction
				)
			))
		);
	}

	/**
	 * doesChannelHaveSubscriber
	 * 
	 * @param string $channel
	 * @param string $username
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function doesChannelHaveSubscriber($channel, $username)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('channels/%s/subscriptions/%s', $channel, $username))
		);
	}

	/**
	 * isUserSubscribedToChannel
	 * 
	 * @param string $username
	 * @param string $channel
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function isUserSubscribedToChannel($username, $channel)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('users/%s/subscriptions/%s', $username, $channel))
		);
	}

	/**
	 * getTeams
	 * 
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getTeams($limit = 25, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'teams', array(
				'query' => array(
					'limit' 		=> $limit,
					'offset' 		=> $offset
				)
			))
		);
	}

	/**
	 * getTeam
	 * 
	 * @param string $team
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getTeam($team)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('teams/%s', $team))
		);
	}

	/**
	 * getUser
	 * 
	 * @param string $username - Optionally specify a username, or get the currently authenticated user
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getUser($username = null)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', $username ? sprintf('users/%s', $username) : 'user')
		);
	}

	/**
	 * getVideo
	 * 
	 * @param string $id
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getVideo($id)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('videos/%s', $id))
		);
	}

	/**
	 * getTopVideos
	 * 
	 * @param string $game
	 * @param string $period
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getTopVideos($game = null, $period = self::PERIOD_WEEK, $limit = 10, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', 'videos/top', array(
				'query' => array(
					'game' 		=> $game,
					'preiod' 	=> $period,
					'limit'		=> $limit,
					'offset' 	=> $offset
				)
			))
		);
	}

	/**
	 * getChannelVideos
	 * 
	 * @param string $channel
	 * @param bool $broadcasts
	 * @param bool $hlds
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */
	public function getChannelVideos($channel, $broadcasts = false, $hlds = false, $limit = 10, $offset = 0)
	{
		return $this->sendRequest(
			$this->httpClient->createRequest('GET', sprintf('channels/%s/videos', $channel), array(
				'query' => array(
					'broadcasts' => $broadcasts ? 'true' : 'false',
					'hlds' 		 => $hlds 		? 'true' : 'false',
					'limit'		 => $limit,
					'offset' 	 => $offset
				)
			))
		);
	}

	/**
	 * sendRequest
	 * 
	 * @param Request $request
	 * 
	 * @throws Exception
	 * 
	 * @return array
	 */ 
	private function sendRequest(Request $request)
	{
		if ($this->getAccessToken()) {
			$request->setHeader('Authorization', sprintf('OAuth %s', $this->getAccessToken()));
		}

		$request->setHeader('Accept', 'application/vnd.twitchtv.v3+json');

		try {
			$response = $this->httpClient->send($request);
		} catch (\Exeption $e) {
			throw new TwitchResponseException($e);
		}

		return $response->json();
	}
}