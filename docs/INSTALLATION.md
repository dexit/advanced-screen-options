# Screen Options - Installation and Setup Guide

This guide provides detailed instructions for installing and configuring ScreenOptions for your WordPress enterprise environment.

## Installation Overview
The installation process involves the following key steps:

## Step 1: Download and Install Plugin

1. Download the latest Screen Options plugin from [GitHub Releases](https://github.com/rtCamp/screen-options/releases)
2. Upload the plugin files to `/wp-content/plugins/screen-options/` on both governing and brand sites
3. If installing from source code, run the following commands in the plugin directory:
   ```bash
   composer install && npm install && npm run build:prod
   ```

## Step 2: Setup Default Screen Options for Site

1. **Activate Plugin:** Go to WordPress Admin → Plugins and activate Screen Options
2. **Setup Credentials:** Navigate to Screen Options → Settings and configure
    - Choose post type or screen options to manage
    - Choose Screen options that you want enabled by default
    - You can also lock the screen options so that users cannot change them
3. **Save Settings:** Click "Save Changes" to apply the configuration

## Step 3: Configure GitHub Actions Workflows

**Add the required workflow files to each brand site repository for automated plugin management.**

1. **Create GitHub Actions Directory:** In each brand site repository, create `.github/workflows/` directory if it doesn't exist

2. **Configure Repository Secrets:** In your GitHub repository settings, add the following secret:
   - **`SCREENOPTIONS_RTCAMP_TOKEN`** - Your GitHub Personal Access Token

3. **Grant Permissions:** Ensure the GitHub token has the following permissions:
   - `repo` - Full repository access
   - `workflow` - Update GitHub Actions workflows
   - `pull_requests:write` - Create and update pull requests

## Configuration Verification

After completing the installation:

1. **Test Connection:** Verify that brand sites appear in the governing site's dashboard
2. **Check API Communication:** Ensure the governing site can communicate with all brand sites
3. **Validate GitHub Integration:** Test that GitHub Actions workflows can be triggered
4. **S3 Configuration:** (If using private plugins) Verify S3 upload functionality

## Troubleshooting Installation

### Common Installation Issues

#### Plugin Not Showing in Governing
- Verify governing site configuration
- Check network connectivity between sites
- Confirm REST API permissions

#### GitHub Actions Not Working
- Ensure GitHub workflows are properly configured
- Verify GitHub PAT token permissions
- Check repository access rights

#### S3 Upload Errors
- Check S3 credentials and bucket permissions
- Verify AWS region configuration
- Ensure bucket allows file uploads

### Getting Help

If you encounter issues during installation:

- **Issues & Bug Reports:** [GitHub Issues](https://github.com/rtCamp/screen-options/issues)
- **Feature Requests:** [GitHub Discussions](https://github.com/rtCamp/screen-options/discussions)
- **Documentation:** [Project Wiki](https://github.com/rtCamp/screen-options/wiki)

## Next Steps

Once installation is complete, refer to the [main README](../README.md) for:
- Usage instructions
- Plugin management features
- Advanced configuration options

---

**Need additional help?** Visit our [GitHub repository](https://github.com/rtCamp/screen-options) for the latest updates and community support.
