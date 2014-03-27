## Extending Sentry Social

Sentry Social 3 was designed from the ground up with extendability in mind.

Extending is as simple as 2 steps:

1. Creating your implementation class.
2. Adding the connection to Sentry Social.

### Creating An Implementation Class

To create an implementation class, you firstly need to determine if you're dealing with OAuth 1 or OAuth 2.

#### OAuth 1

To create an OAuth 1 implementation class for Sentry Social 3, simply create a class which extends `League\OAuth1\Client\Server\Server`.

Example:

	use League\OAuth1\Client\Server\User;

    class MyOAuth1Provider extends League\OAuth1\Client\Server\Server {
    
    	/**
	     * The response type for data returned from API calls.
	     *
	     * @var string
	     */
	    protected $responseType = 'json';
    
	    /**
	     * Get the URL for retrieving temporary credentials.
	     *
	     * @return string
	     */
	    public function urlTemporaryCredentials()
	    {
	    	return 'https://api.myprovider.com/oauth/temporary_credentials';
	    }
	
	    /**
	     * Get the URL for redirecting the resource owner to authorize the client.
	     *
	     * @return string
	     */
	    public function urlAuthorization()
	    {
	    	return 'https://api.myprovider.com/oauth/authorize';
	    }
	
	    /**
	     * Get the URL retrieving token credentials.
	     *
	     * @return string
	     */
	    public function urlTokenCredentials()
	    {
	    	return 'https://api.myprovider.com/oauth/token_credentials';
	    }
	
	    /**
	     * Get the URL for retrieving user details.
	     *
	     * @return string
	     */
	    public function urlUserDetails()
	    {
	    	return 'https://api.myprovider/1.0/user.json';
	    }
	
	    /**
	     * Take the decoded data from the user details URL and convert
	     * it to a User object.
	     *
	     * @param  mixed  $data
	     * @param  TokenCredentials  $tokenCredentials
	     * @return User
	     */
	    public function userDetails($data, TokenCredentials $tokenCredentials)
	    {
	    	$user = new User;
	    	
	    	// Take the decoded data (determined by $this->responseType)
	    	// and fill out the user object by abstracting out the API
	    	// properties (this keeps our user object simple and adds
	    	// a layer of protection in-case the API response changes)
	    	$user->first_name = $data['user']['firstname'];
	    	$user->last_name = $data['user']['lastname'];
	    	$user->email = $data['emails']['primary'];
	    	// Etc..
	    	
	    	return $user;
	    }
	
	    /**
	     * Take the decoded data from the user details URL and extract
	     * the user's UID.
	     *
	     * @param  mixed  $data
	     * @param  TokenCredentials  $tokenCredentials
	     * @return string|int
	     */
	    public function userUid($data, TokenCredentials $tokenCredentials)
	    {
	    	return $data['unique_id'];
	    }
	
	    /**
	     * Take the decoded data from the user details URL and extract
	     * the user's email.
	     *
	     * @param  mixed  $data
	     * @param  TokenCredentials  $tokenCredentials
	     * @return string
	     */
	    public function userEmail($data, TokenCredentials $tokenCredentials)
	    {
	    	// Optional
	    	if (isset($data['email']))
	    	{
	    		return $data['email'];
	    	}
	    }
	
	    /**
	     * Take the decoded data from the user details URL and extract
	     * the user's screen name.
	     *
	     * @param  mixed  $data
	     * @param  TokenCredentials  $tokenCredentials
	     * @return User
	     */
	    public function userScreenName($data, TokenCredentials $tokenCredentials)
	    {
	    	// Optional
	    	if (isset($data['screen_name']))
	    	{
	    		return $data['screen_name'];
	    	}
	    }
    }

#### OAuth 2

Creating an OAuth 2 implementation is much the same, however just a little easier (as there's less methods to implement):

	use League\OAuth2\Client\Provider\User;

    class MyOAuth2Provider extends League\OAuth2\Client\Provider\IdentityProvider {

		// Default scopes
	    public $scopes = array('scope1', 'scope2');
	    
	    // Response type
        public $responseType = 'json';

        public function urlAuthorize()
        {
            return 'https://api.myprovider.com/authorize';
        }

        public function urlAccessToken()
        {
            return 'https://api.myprovider.com/access_token';
        }

        public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
        {
            return 'https://api.myprovider.com/1.0/user.json?access_token='.$token;
        }

        public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
        {
            $user = new User;
	    	
	    	// Take the decoded data (determined by $this->responseType)
	    	// and fill out the user object by abstracting out the API
	    	// properties (this keeps our user object simple and adds
	    	// a layer of protection in-case the API response changes)
	    	$user->first_name = $response['user']['firstname'];
	    	$user->last_name = $response['user']['lastname'];
	    	$user->email = $response['emails']['primary'];
	    	// Etc..
	    	
	    	return $user;
        }

        public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
        {
            return $response['unique_id'];
        }

        public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
        {
            // Optional, however OAuth2 usually provides a scope
            // to receive access to a user's email, you should always
            // ask for this scope, as having an email is awesome.
	    	if (isset($response['email']))
	    	{
	    		return $response['email'];
	    	}
        }

        public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
        {
            // Optional
	    	if (isset($response['screen_name']))
	    	{
	    		return $response['screen_name'];
	    	}
        }
	}

### Adding a Connection

Now that you've made an implementation class for Sentry Social 3, you need to add a connection.

#### In Laravel

In Laravel, the easiest way is to add the connection to your config:

	// After publishing your config, this is in app/config/packages/cartalyst/sentry-social/config.php
	'connections' => array(

		// Additional, default connections…
		
		'myprovider' => array(
		
			// The driver should match your implementation's name (including namespace)
			'driver' => 'MyOAuth2Provider',
			
			'identifier' => 'your-app-identifier',
			'secret' => 'your-app-secret',
			
			// To override OAuth2 scopes (scopes don't exist on OAuth 1), specify
			// this parameter. Otherwise, the default scopes from your implementation
			// class will be used.
			'scopes' => array('scope1', 'scope2', 'scope3'),
		),
	),

#### Outside Laravel

Outside of Laravel, you can add a connection by calling a method on your `$sentrySocial` object :

	$sentrySocial->addConnection('myprovider', array(
	
		// See the comments for "In Laravel" above, these parameters
		// are the same
		'driver' => 'MyOAuth2Provider',
		'identifier' => 'your-app-identifier',
		'secret' => 'your-app-secret',
		'scopes' => array('scope1', 'scope2', 'scope3'),
	));

Now, continue to use Sentry Social as normal, instead substituting `myprovider` (or whatever you named your connection as) when authorizing and authenticating!

### Tips

Once you've made a provider, if it could potentially be used by anybody, you should endeavour to submit a pull request back to the underlying [OAuth 1](http://github.com/php-loep/oauth1-client) or [OAuth2](http://github.com/php-loep/oauth2-client) repository, so everybody can make use of it.

Once that pull request has been merged, you may submit a pull request back to Sentry Social, to simply update the default config file to ship with your provider built-in!
