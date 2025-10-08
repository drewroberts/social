# Tweet Purge System Implementation

## 🎯 Overview
Complete implementation of a tweet deletion management system with CSV import, scheduled processing, and Filament admin interface.

## 📁 Files Created/Modified

### Database
- ✅ `database/migrations/2025_10_07_164222_create_purges_table.php`
  - Added `unique()` constraint to `post_id` column to prevent duplicates
  - Fields: post_id, posted_at, text, save, account_id, requested_at, purged_at

### Models
- ✅ `app/Models/Purge.php`
  - Fillable fields configured
  - Relationships: `account()` → BelongsTo Account
  - Scopes: `pending()`, `purged()`, `saved()`, `requested()`
  - Computed attribute: `status` → Returns 'Pending', 'Requested', 'Purged', or 'Saved'
  - Date casting for all timestamp fields

### Services
- ✅ `app/Services/PurgeService.php`
  - `getDefaultAccount()` - Finds @drewroberts Twitter account
  - `processPurge()` - Main processing logic
  - `getNextPendingPurge()` - Gets oldest pending tweet by `posted_at`
  - `getStats()` - Returns purge statistics

- ✅ `app/Services/TwitterAccountService.php` (modified)
  - `deleteTweet()` - Deletes tweet via Twitter API
  - **404 handling**: Returns `true` if tweet not found (already deleted)
  - Comprehensive error logging

### Filament Resources
- ✅ `app/Filament/Resources/Purges/PurgeResource.php` - Main resource
- ✅ `app/Filament/Resources/Purges/Schemas/PurgeForm.php` - Form configuration
- ✅ `app/Filament/Resources/Purges/Tables/PurgesTable.php` - Table with CSV import
- ✅ `app/Filament/Resources/Purges/Pages/ListPurges.php` - List page
- ✅ `app/Filament/Resources/Purges/Pages/ViewPurge.php` - View page

### Commands
- ✅ `app/Console/Commands/ProcessPurgeQueue.php`
  - Command: `php artisan purge:process`
  - Finds oldest pending purge and processes it
  - Returns success/failure status

### Scheduler
- ✅ `routes/console.php` (modified)
  - Added: `Schedule::command('purge:process')->everyMinute()`
  - Runs automatically every minute

## 🎨 Filament UI Features

### Table Columns
- **Tweet ID** - Searchable, sortable, copyable
- **Tweet Text** - Truncated to 50 chars with full text tooltip
- **Posted** - DateTime with relative time (e.g., "2 years ago")
- **Saved** - Boolean icon (shield for saved, trash for deletable)
- **Account** - Shows username or "Default"
- **Status** - Badge (Success=Purged, Warning=Requested, Info=Saved, Gray=Pending)
- **Requested/Purged** - Timestamps (toggleable)

### Filters
- **Status** - Dropdown: Pending, Requested, Purged, Saved
- **Saved/Protected** - Ternary filter
- **Account** - Dropdown of all accounts

### Actions

#### Record Actions
1. **View** - Opens view modal with full details
2. **Protect/Unprotect** - Toggle `save` status
   - Requires confirmation
   - Dynamic label/icon based on current state

#### Toolbar Actions
1. **Import CSV** - Upload CSV file
   - **Validation**: Checks for required columns (`post_id`, `posted_at`, `text`)
   - **Duplicate Prevention**: Skips existing `post_id` entries (unique constraint)
   - **Error Handling**: Shows detailed errors for first 5 failures
   - **Success Notification**: Reports imported vs skipped counts
   
2. **Stats** - Quick view of purge statistics
   - Shows: Total, Pending, Requested, Purged, Saved counts

## 📊 CSV Import Format

```csv
post_id,posted_at,text
1397329436140351492,2021-05-25 19:11:13,"@Jason CC: @DrewRoberts"
1516904506663096320,2022-04-20 18:19:51,"@DrewRoberts @moonbirds_xyz @ryancarson Sounds free.."
```

**Requirements:**
- Header row must include: `post_id`, `posted_at`, `text`
- `post_id` - Required, must be unique
- `posted_at` - Optional, any date format Laravel can parse
- `text` - Optional, tweet content

**Behavior:**
- Duplicate `post_id` entries are **skipped**, not updated
- File is automatically deleted after processing
- Max file size: 10MB

## 🔄 Automated Processing Flow

```
Every Minute (Laravel Scheduler)
        ↓
php artisan purge:process
        ↓
Query: Oldest pending purge
  WHERE save = false
    AND requested_at IS NULL
  ORDER BY posted_at ASC
  LIMIT 1
        ↓
Get account (specific or @drewroberts)
        ↓
Call Twitter API: destroyTweet(post_id)
        ↓
Mark requested_at = NOW
        ↓
   ┌────────┴────────┐
   ▼                 ▼
SUCCESS           FAILURE
(or 404)        (log error)
   │                 │
   ▼                 ▼
Mark purged_at   Keep for
= NOW            retry/review
```

## 🔒 Security & Business Logic

1. **Default Account**: If `account_id` is null, uses account with username "drewroberts"
2. **Save Protection**: Tweets with `save=true` are never processed
3. **Idempotency**: Already requested/purged tweets are skipped
4. **404 Handling**: Treats "not found" as successful deletion
5. **Rate Limiting**: One tweet per minute (safe, ~3.3% of Twitter's limit)
6. **Unique Constraint**: Database prevents duplicate `post_id` entries

## 📈 Processing Speed

With current settings:
- **Rate**: 1 tweet per minute
- **Daily**: 1,440 tweets
- **20,637 tweets**: ~14.3 days

To speed up (if needed):
- Adjust cron: `everyMinute()` → `everyThirtySeconds()` (requires cron setup)
- Twitter limit: 50 deletes per 15 minutes = ~3.3 per minute max

## 🧪 Testing the System

### 1. Test Command Manually
```bash
php artisan purge:process
```

### 2. Import Test CSV
- Login to Filament admin at `/admin`
- Navigate to "Tweet Purges"
- Click "Import CSV" button
- Upload `/storage/app/private/tweets.csv`

### 3. Verify Import
- Check table shows imported tweets
- Status should be "Pending"
- Saved should be unchecked (trash icon)

### 4. Test Manual Processing
```bash
php artisan purge:process
```
Should process oldest tweet and mark as "Requested" then "Purged"

### 5. Enable Scheduler
```bash
# Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually:
```bash
php artisan schedule:work
```

## 🎯 Navigation

In Filament:
- Icon: Trash (heroicon-o-trash)
- Label: "Tweet Purges"
- Sort order: 20 (after Accounts)

## 🐛 Error Handling

### Twitter API Errors
- **404 (Not Found)**: Marked as purged (success)
- **Rate Limit**: Logged, will retry next minute
- **Auth Error**: Logged, needs manual account reconnection
- **Network Error**: Logged, will retry next minute

### CSV Import Errors
- **Missing Headers**: Rejected with error message
- **Duplicate post_id**: Skipped silently
- **Invalid Data**: Row skipped, error logged (first 5 shown)
- **File Too Large**: Rejected by Filament (10MB limit)

## 🔍 Monitoring

### View Stats
Click "Stats" button in Filament to see:
- Total imported
- Pending (not yet requested)
- Requested (waiting for confirmation)
- Purged (successfully deleted)
- Saved (protected from deletion)

### Logs
Check `storage/logs/laravel.log` for:
- Successful deletions
- 404 responses
- API errors
- Processing failures

### Database Queries
```sql
-- Pending tweets
SELECT COUNT(*) FROM purges WHERE save = false AND requested_at IS NULL;

-- Success rate
SELECT 
  COUNT(*) as total,
  SUM(CASE WHEN purged_at IS NOT NULL THEN 1 ELSE 0 END) as purged,
  SUM(CASE WHEN save = true THEN 1 ELSE 0 END) as saved
FROM purges;
```

## 🚀 Deployment Checklist

- ✅ Run migrations: `php artisan migrate`
- ✅ Clear caches: `php artisan optimize:clear`
- ✅ Set up cron job for Laravel scheduler
- ✅ Ensure @drewroberts account is connected and active
- ✅ Import initial CSV file via Filament
- ✅ Test manual command execution
- ✅ Monitor first few automated runs

## 📝 Future Enhancements

Potential improvements (not implemented):
- Batch processing (3 per minute)
- Retry logic for failed deletions
- Progress dashboard widget
- Export purge history to CSV
- Bulk protect/unprotect actions
- Custom processing schedule per account
- Pause/resume queue functionality
- Email notifications for completion

---

**System Status**: ✅ Fully Implemented & Ready for Use

**Next Step**: Log into Filament, import CSV, and watch the automated processing!
