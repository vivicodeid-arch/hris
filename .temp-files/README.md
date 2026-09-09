# Temporary Files Directory

This directory contains temporary files and test scripts that are not part of the production codebase.

## Folder Structure

### `/test-logs`

Contains test files and debug logs:

- `test_update_api.php` - API update testing script
- `check_debug_log.php` - Debug log checking utility

### `/temp-scripts`

Contains temporary scripts and configuration files:

- `temp_script.js` - Temporary JavaScript files
- `temp_script.txt` - Text-based temporary scripts
- `temp_script_content.txt` - Script content backup
- `temp_script_final.js` - Final version of temp scripts
- `temp_script_processed.js` - Processed script versions
- `temp_scripts.txt` - Collection of temporary scripts

## Guidelines

1. **Do NOT commit** - This folder is ignored in `.gitignore`
2. **Temporary Only** - Files here should be cleaned up when no longer needed
3. **Local Development** - Use this space for local testing and debugging
4. **Not for Production** - These files should never be deployed to production

## Cleanup

Regularly review and delete files that are no longer needed to keep the repository clean.

Last Organized: February 22, 2026
