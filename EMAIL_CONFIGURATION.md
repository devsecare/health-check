# Email Configuration Guide

This application sends email reports for various website analysis tests. By default, emails are logged to files instead of being sent via SMTP.

## Current Email Features

The system can send email reports for:
- ✅ **Broken Links Checks** - Detailed report of broken links found
- ✅ **PageSpeed Insights** - Performance scores and Core Web Vitals
- ✅ **SEO Audits** - SEO analysis and recommendations
- ✅ **Domain Authority** - Domain authority metrics and backlink data

## Configuration

### Default Configuration (Log Only)

Currently, the system is configured to log emails instead of sending them. This is set in `config/mail.php`:

```php
'default' => env('MAIL_MAILER', 'log'),
```

Emails will be written to `storage/logs/laravel.log` instead of being sent.

### Setting Up SMTP for Real Email Delivery

To send actual emails, you need to configure SMTP settings in your `.env` file:

#### Option 1: Gmail SMTP

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** For Gmail, you'll need to:
1. Enable 2-Step Verification
2. Generate an App Password (not your regular password)
3. Use the App Password in `MAIL_PASSWORD`

#### Option 2: Mailgun

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-secret
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Option 3: SendGrid

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Option 4: Custom SMTP Server

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Testing Email Configuration

After updating your `.env` file, test the email configuration:

1. Run a test (PageSpeed, SEO Audit, etc.) with "Send email report" checked
2. Check your email inbox
3. If emails don't arrive, check `storage/logs/laravel.log` for errors

### Email Recipients

- **Broken Links**: Sent to the authenticated user who initiated the check
- **PageSpeed Insights**: Sent to the authenticated user who ran the test
- **SEO Audit**: Sent to the authenticated user who ran the audit
- **Domain Authority**: Sent to the authenticated user who ran the check

All emails are sent to the logged-in user's email address when they check the "Send email report" option.

## Troubleshooting

### Emails Not Sending

1. **Check `.env` file**: Ensure all SMTP settings are correct
2. **Check logs**: Review `storage/logs/laravel.log` for email errors
3. **Test connection**: Verify SMTP credentials are correct
4. **Check spam folder**: Emails might be filtered as spam
5. **Verify MAIL_FROM_ADDRESS**: Some providers require verified sender addresses

### Common Errors

- **"Connection timeout"**: Check firewall settings and SMTP port
- **"Authentication failed"**: Verify username/password are correct
- **"Could not instantiate mailer"**: Check MAIL_MAILER value in `.env`

## Security Notes

- Never commit `.env` file to version control
- Use App Passwords for Gmail (not your regular password)
- Consider using environment-specific email services for production
- Regularly rotate email service API keys/passwords
