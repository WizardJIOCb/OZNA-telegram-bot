# FIO Display Fix Summary

## Issue
The access requests were displaying the user's Telegram username instead of their FIO (Full Name) from their profile.

## Root Cause
The system was correctly retrieving the user's FIO from their profile, but it was still using the Telegram username as the primary display name in notifications.

## Changes Made

### 1. AdminService::sendAccessRequest Method
- Modified to use the user's FIO as the primary display name when available
- The `user_name` field in the request data now contains the FIO when available, falling back to Telegram username
- Updated the notification message to consistently display the FIO when available

### 2. AdminHandler::showAccessRequestDetails Method
- Improved the logic for displaying Telegram username information
- Now only shows the Telegram username when it's different from the display name (FIO)
- Cleaned up redundant display logic

## Verification
The changes ensure that:
1. When a user has set their FIO in their profile, it will be displayed instead of their Telegram username
2. When a user hasn't set their FIO, their Telegram username will be displayed as before
3. The Telegram username is only shown as additional information when it differs from the display name

## Testing
To test this fix:
1. Ensure a user has set their FIO using the /fio command
2. Have that user submit an access request
3. Check that the admin notification shows the FIO instead of the Telegram username
4. Verify that the access request details also show the FIO