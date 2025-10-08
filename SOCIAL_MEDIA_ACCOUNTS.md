# Multi-Account Social Media Management System

## Overview

This system allows users to connect and manage multiple social media accounts (Twitter, Facebook, Telegram) through OAuth authentication.

## Architecture

### Core Components

1. **Models**
   - `User`: The authenticated user who owns the social media accounts
   - `Account`: Represents a connected social media account with encrypted OAuth tokens

2. **Enums**
   - `SocialService`: Defines available social media platforms (TWITTER, FACEBOOK, TELEGRAM)
   - Includes helper methods for labels, icons, and OAuth version detection

3. **Services**
   - `SocialAccountService` (Interface): Defines contract for all social platform integrations
   - `TwitterAccountService`: Complete OAuth 1.0a implementation for Twitter

4. **Database**
   - `accounts` table: Stores connected accounts with encrypted tokens, metadata, and status

### OAuth Flow

#### Twitter (OAuth 1.0a)

1. **Initiate Connection**:
   - User clicks "Connect Twitter" button in Filament AccountResource
   - Routes to `/social/twitter/connect`
   - `TwitterAccountService::initiateOAuth()` gets request token from Twitter
   - Stores temporary OAuth tokens in session
   - Redirects user to Twitter authorization page

2. **Handle Callback**:
   - Twitter redirects back to `/social/twitter/callback` with `oauth_verifier`
   - `TwitterAccountService::handleCallback()` retrieves session tokens
   - Exchanges verifier for permanent access token
   - Fetches user credentials (including email)
   - Creates/updates Account record with encrypted tokens
   - Stores metadata (profile image, follower counts, etc.)
   - Clears session and redirects to dashboard with success message

3. **Post Content**:
   - `TwitterAccountService::post()` accepts account, status text, and optional media
   - Uses `Twitter::usingCredentials()` to swap in account-specific tokens
   - Supports media uploads
   - Updates `last_synced_at` timestamp

4. **Verify Credentials**:
   - `TwitterAccountService::verifyCredentials()` checks if tokens are still valid
   - Updates account metadata if successful
   - Returns boolean status

5. **Disconnect**:
   - `TwitterAccountService::disconnect()` sets `is_active` to false
   - Nulls out token fields (Twitter OAuth 1.0a has no revoke endpoint)

### Security Features

- **Encrypted Tokens**: All OAuth tokens (access_token, access_token_secret, refresh_token) use Laravel's encrypted casting
- **User Isolation**: Filament resource automatically filters accounts by authenticated user
- **Session-Based OAuth**: Temporary OAuth tokens stored in session during callback flow
- **Unique Constraints**: Database prevents duplicate account connections (user_id + service + service_user_id)

### Filament UI

#### AccountResource

- **List View**: Shows all connected accounts with service icon, username, active status, token status, last sync time
- **Filters**: Filter by service, active status, expired tokens, needs refresh
- **Actions**:
  - **Reconnect**: Re-initiates OAuth flow for inactive/expired accounts
  - **Verify**: Checks credential validity and updates metadata
  - **Disconnect**: Deactivates account and clears tokens
- **Empty State**: Displays "Connect Twitter" button when no accounts connected

#### Token Status Badges

- **Valid** (Green): Token is active and not expiring soon
- **Needs Refresh** (Orange): Token expires within 24 hours (OAuth 2.0 only)
- **Expired** (Red): Token has expired and needs reconnection

### Routes

```php
// All routes require authentication
Route::middleware(['auth'])->prefix('social')->name('social.')->group(function () {
    // Initiate OAuth flow
    Route::get('{service}/connect', [SocialAuthController::class, 'connect'])->name('connect');
    
    // Handle OAuth callback
    Route::get('{service}/callback', [SocialAuthController::class, 'callback'])->name('callback');
    
    // Disconnect account
    Route::delete('{service}/accounts/{accountId}', [SocialAuthController::class, 'disconnect'])->name('disconnect');
});
```

### Configuration

#### Twitter API Setup

1. Go to https://developer.twitter.com/
2. Create a new app or use existing app
3. Enable "Request email address from users" in app permissions
4. Add callback URL: `http://your-domain.com/social/twitter/callback`
5. Copy Consumer Key and Consumer Secret to `.env`:

```env
TWITTER_CONSUMER_KEY=your_consumer_key
TWITTER_CONSUMER_SECRET=your_consumer_secret
TWITTER_API_VERSION=1.1
```

Note: `TWITTER_ACCESS_TOKEN` and `TWITTER_ACCESS_TOKEN_SECRET` in `.env` are for the app-level credentials used to initiate OAuth, not user tokens. User tokens are stored encrypted in the database.

#### Facebook API Setup (Coming Soon)

- Will use OAuth 2.0 flow
- Requires Facebook App ID and App Secret
- Token refresh logic for expiring tokens

#### Telegram API Setup (Coming Soon)

- Will use OAuth 2.0 flow
- Requires Telegram Bot Token
- Different authentication flow than traditional OAuth

### Database Schema

```php
Schema::create('accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('service'); // 'twitter', 'facebook', 'telegram'
    $table->string('service_user_id')->nullable(); // Platform's user ID
    $table->string('username')->nullable(); // @username or display name
    $table->text('access_token')->nullable(); // Encrypted
    $table->text('access_token_secret')->nullable(); // Encrypted (OAuth 1.0a only)
    $table->text('refresh_token')->nullable(); // Encrypted (OAuth 2.0 only)
    $table->timestamp('token_expires_at')->nullable(); // OAuth 2.0 token expiry
    $table->json('scopes')->nullable(); // OAuth scopes granted
    $table->boolean('is_active')->default(true);
    $table->json('metadata')->nullable(); // Profile data (image, followers, etc.)
    $table->timestamp('last_synced_at')->nullable();
    $table->timestamps();
    
    // Indexes
    $table->index(['user_id', 'service']);
    $table->index(['service', 'service_user_id']);
    $table->unique(['user_id', 'service', 'service_user_id']); // Prevent duplicates
});
```

### Usage Examples

#### Connect a Twitter Account

1. Navigate to Connected Accounts in Filament admin panel
2. Click "Connect Twitter" button
3. Authorize app on Twitter
4. Redirected back with success message
5. Account appears in list with token status

#### Post a Tweet

```php
use App\Services\TwitterAccountService;

$twitter = app(TwitterAccountService::class);
$account = Account::where('service', SocialService::TWITTER)->first();

// Simple text tweet
$response = $twitter->post($account, "Hello from my app!");

// Tweet with media
$response = $twitter->post($account, "Check out this image!", [
    'media' => ['/path/to/image.jpg']
]);
```

#### Verify Account Credentials

```php
use App\Services\TwitterAccountService;

$twitter = app(TwitterAccountService::class);
$account = Account::find(1);

if ($twitter->verifyCredentials($account)) {
    // Credentials are valid, metadata updated
} else {
    // Credentials invalid, may need reconnection
}
```

### Future Enhancements

1. **Post Scheduling**: Create a `posts` table and queue scheduled tweets
2. **Analytics**: Track post performance and engagement metrics
3. **Multi-Service Posting**: Post the same content to multiple services at once
4. **Thread Support**: Create tweet threads with multiple related posts
5. **Media Library**: Store and reuse media across multiple posts
6. **Webhook Integration**: Real-time notifications for mentions, replies, etc.

## Testing

Run the test suite:

```bash
php artisan test
```

### Test Coverage

- Email domain validation (7 unit tests)
- Registration flow with domain checking (8 feature tests)
- Account OAuth flow (coming soon)
- Service implementations (coming soon)

## Troubleshooting

### "Failed to get request token from Twitter"

- Check that `TWITTER_CONSUMER_KEY` and `TWITTER_CONSUMER_SECRET` are set correctly in `.env`
- Verify callback URL is registered in Twitter Developer Portal
- Ensure app has "Request email address from users" permission enabled

### "Token expired" in account list

- Click "Reconnect" button to re-authorize the account
- For OAuth 2.0 services (Facebook/Telegram), system will auto-refresh if within 24 hours

### "Could not verify account credentials"

- Account may have been revoked/disconnected on the platform side
- Try reconnecting the account
- Check platform's app authorization page for user

## Security Considerations

- All OAuth tokens are encrypted in database using Laravel's encrypted casting
- Session-based OAuth prevents CSRF attacks during callback
- User isolation ensures users can only access their own accounts
- Unique constraints prevent duplicate account connections
- No plain-text tokens ever stored or logged
- Access tokens never exposed in UI or API responses
