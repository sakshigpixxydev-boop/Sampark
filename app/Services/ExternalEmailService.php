<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ExternalEmailService
{
  protected $baseUrl = 'https://mail.ipdc.org/api/v1';
  protected $token = 'BCOZsHYQnHN0fFnEX4Hyx2MgYxlJkvE5hNpX9Qed7_biEnv1bSB'; // Hardcoded initial token as per curl.
  public $lastResponse = null;

  /**
   * Get Mail Access Token with Cache & Timeout Support
   */
  public function getMailAccessToken()
  {
    // ==========================================
    // METHOD 1: Gmail SMTP (Currently Active)
    // ==========================================
    // Gmail SMTP does not require an external access token.
    return 'gmail_smtp_active';

    /*
    // ==========================================
    // METHOD 2: Third-Party Email Service API (Commented Out)
    // ==========================================
    $cacheKey = 'external_mail_access_token';

    try {
      $token = Cache::get($cacheKey);
      if ($token) {
        return $token;
      }

      $response = Http::withToken($this->token)
        ->timeout(5)
        ->asForm()
        ->post("{$this->baseUrl}/user/mail_access_token", [
          'email' => 'helloatbhavesh@gmail.com',
          'user_type' => '4',
          'password' => 'Hello@bhavesh123'
        ]);

      if ($response->successful()) {
        $data = $response->json();
        $accessToken = $data['token'] ?? $data['access_token'] ?? null;
        if ($accessToken) {
          // Cache the token for 60 minutes
          Cache::put($cacheKey, $accessToken, 3600);
          return $accessToken;
        }
      }

      Log::error('Mail Access Token Request Failed: ' . $response->body());
      return null;
    } catch (\Exception $e) {
      Log::error('Mail Access Token Exception: ' . $e->getMessage());
      return null;
    }
    */
  }

  /**
   * Send SMTP Mail with Retry & Timeout Support
   * 
   * @param array $to Recipient array [['email' => 'name'], ...] or ['email' => 'name']
   * @param string $subject
   * @param string $messageHtml
   * @param string|null $cc
   * @param string|null $filepath Path to attachment
   */
  public function sendSmtpMail(array $to, string $subject, string $messageHtml, string $cc = null, $filepath = null)
  {
    // ✅ Validate recipient
    if (empty($to)) {
      Log::error('Recipient email is empty.');
      return false;
    }

    // Validate the first recipient email in the array
    if (is_array($to)) {
      $firstKey = array_key_first($to);
      // Handle both [['email' => 'name']] and ['email' => 'name'] formats
      $emailToValidate = is_numeric($firstKey) && is_array($to[$firstKey])
        ? array_key_first($to[$firstKey])
        : (is_numeric($firstKey) ? $to[$firstKey] : $firstKey);

      if (!filter_var($emailToValidate, FILTER_VALIDATE_EMAIL)) {
        Log::error('Invalid recipient email in array: ' . $emailToValidate);
        return false;
      }
    }

    // ✅ Validate from email (optional — fallback to env)
    $fromEmail = config('mail.from.address', 'helloatbhavesh@gmail.com');
    $fromName = config('mail.from.name', 'PRAMUKH Academy');

    $headerHtml = self::getEmailHeader();
    $footerHtml = self::getEmailFooter();

    $finalMessage = $headerHtml . ($messageHtml ?? '') . $footerHtml;

    $token = $this->getMailAccessToken();
    if (!$token) {
      Log::error('SMTP Email failed: Could not retrieve access token.');
      return false;
    }

    return $this->executeSendMail($token, $to, $subject, $finalMessage, $cc, $filepath, true, false);
  }

  /**
   * Send SMTP Mail Directly (Bypassing Queue) with Retry & Timeout Support
   * 
   * @param array $to Recipient array [['email' => 'name'], ...] or ['email' => 'name']
   * @param string $subject
   * @param string $messageHtml
   * @param string|null $cc
   * @param string|null $filepath Path to attachment
   */
  public function sendSmtpMailDirect(array $to, string $subject, string $messageHtml, string $cc = null, $filepath = null)
  {
    // ✅ Validate recipient
    if (empty($to)) {
      Log::error('Recipient email is empty.');
      return false;
    }

    // Validate the first recipient email in the array
    if (is_array($to)) {
      $firstKey = array_key_first($to);
      // Handle both [['email' => 'name']] and ['email' => 'name'] formats
      $emailToValidate = is_numeric($firstKey) && is_array($to[$firstKey])
        ? array_key_first($to[$firstKey])
        : (is_numeric($firstKey) ? $to[$firstKey] : $firstKey);

      if (!filter_var($emailToValidate, FILTER_VALIDATE_EMAIL)) {
        Log::error('Invalid recipient email in array: ' . $emailToValidate);
        return false;
      }
    }

    // ✅ Validate from email (optional — fallback to env)
    $fromEmail = config('mail.from.address', 'helloatbhavesh@gmail.com');
    $fromName = config('mail.from.name', 'PRAMUKH Academy');

    $headerHtml = self::getEmailHeader();
    $footerHtml = self::getEmailFooter();

    $finalMessage = $headerHtml . ($messageHtml ?? '') . $footerHtml;

    $token = $this->getMailAccessToken();
    if (!$token) {
      Log::error('SMTP Email failed: Could not retrieve access token.');
      return false;
    }

    return $this->executeSendMail($token, $to, $subject, $finalMessage, $cc, $filepath, true, true);
  }

  /**
   * Execute the send mail request to Main Email Service API
   */
  protected function executeSendMail(string $token, array $to, string $subject, string $finalMessage, string $cc = null, $filepath = null, bool $retryOnUnauthorized = true, bool $direct = false)
  {
    // ==========================================
    // METHOD 1: Gmail SMTP (Currently Active)
    // ==========================================
    try {
      $formattedTo = [];
      foreach ($to as $key => $value) {
        if (is_array($value)) {
          foreach ($value as $k => $v) {
            $formattedTo[] = [$k => $v];
          }
        } else if (is_numeric($key)) {
          $formattedTo[] = [$value => ''];
        } else {
          $formattedTo[] = [$key => $value];
        }
      }

      $fromEmail = 'no-reply@pramukh.academy';
      $gmailPassword = str_replace(' ', '', 'BKQNZI/WT4Se7RSVDilbYWY2tSYd2eLFWWDoYCAr6/wH');
      $fromName = config('mail.from.name', 'PRAMUKH Academy');

      // Dynamically configure a separate 'gmail' mailer to avoid affecting default settings
      config([
        'mail.mailers.gmail' => [
          'transport' => 'smtp',
          'host' => 'email-smtp.us-east-1.amazonaws.com',
          'port' => 465,
          'encryption' => 'ssl',
          'username' => 'AKIARL3X7Z2R356OU775',
          'password' => $gmailPassword,
          'timeout' => null,
        ],
      ]);

      Mail::mailer('gmail')->html($finalMessage, function ($message) use ($formattedTo, $subject, $cc, $filepath, $fromEmail, $fromName) {
        $message->from($fromEmail, $fromName);
        $message->subject($subject ?? 'Notification');

        foreach ($formattedTo as $recipient) {
          foreach ($recipient as $email => $name) {
            if (!empty($name)) {
              $message->to($email, $name);
            } else {
              $message->to($email);
            }
          }
        }

        if (!empty($cc)) {
          $ccEmails = array_map('trim', explode(',', $cc));
          foreach ($ccEmails as $ccEmail) {
            if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
              $message->cc($ccEmail);
            }
          }
        }

        if (!empty($filepath)) {
          $files = is_array($filepath) ? $filepath : [$filepath];
          foreach ($files as $file) {
            if (File::exists($file)) {
              $message->attach($file);
            }
          }
        }
      });

      Log::info('Email sent via Gmail SMTP to: ' . json_encode($to));
      return true;

    } catch (\Exception $e) {
      $this->lastResponse = $e->getMessage();
      Log::error('Gmail SMTP Email Exception: ' . $this->lastResponse);
      return false;
    }

    /*
    // ==========================================
    // METHOD 2: Third-Party Email Service API (Commented Out)
    // ==========================================
    try {
      $formattedTo = [];
      foreach ($to as $key => $value) {
        if (is_array($value)) {
          foreach ($value as $k => $v) {
            $formattedTo[] = [$k => $v];
          }
        } else if (is_numeric($key)) {
          $formattedTo[] = [$value => ''];
        } else {
          $formattedTo[] = [$key => $value];
        }
      }

      $fromEmail = config('mail.from.address', 'helloatbhavesh@gmail.com');
      $fromName = config('mail.from.name', 'PRAMUKH Academy');
      $notificationEmail = json_encode([$fromEmail => $fromName]);

      $request = Http::withToken($token)->timeout(60);

      if (!empty($filepath)) {
        $files = is_array($filepath) ? $filepath : [$filepath];
        $fileKey = count($files) > 1 ? 'file[]' : 'file';
        foreach ($files as $file) {
          if (File::exists($file)) {
            $request = $request->attach(
              $fileKey,
              file_get_contents($file),
              basename($file)
            );
          }
        }
      }

      if (empty($filepath)) {
        $request = $request->asMultipart();
      }

      $response = $request->post("{$this->baseUrl}/user/send_smtp_mail", [
        'notification_email' => $notificationEmail,
        'cc' => $cc ?? '',
        'to' => json_encode($formattedTo),
        'subject' => $subject ?? 'Notification',
        'message' => $finalMessage,
        'notification_name' => $fromName,
        'no_template' => '1',
        'direct' => $direct ? '1' : '0'
      ]);

      if ($response->status() === 401 && $retryOnUnauthorized) {
        Log::warning('SMTP Email API returned 401 Unauthorized. Clearing token cache and retrying...');
        Cache::forget('external_mail_access_token');
        $newToken = $this->getMailAccessToken();
        if ($newToken) {
          return $this->executeSendMail($newToken, $to, $subject, $finalMessage, $cc, $filepath, false, $direct);
        }
      }

      if ($response->successful()) {
        $body = $response->body();
        $this->lastResponse = $body;

        // Parse and check body for hidden errors (e.g. DLMS API returning success=true with cURL timeout error)
        $data = $response->json();
        if (is_array($data)) {
          $success = ($data['success'] ?? false) || in_array($data['status'] ?? '', ['sent', 'queued', 'queued_rate_limit'], true);
          $hasCurlError = isset($data['curl_response']) && stripos($data['curl_response'], 'curl error') !== false;

          if (!$success || $hasCurlError) {
            Log::error('SMTP Email API returned success status but body indicates failure or internal timeout: ' . $body);
            return false;
          }
        }

        Log::info('Email sent via External API to: ' . json_encode($to));
        return true;
      }

      $this->lastResponse = $response->body();
      Log::error('SMTP Email API Request Failed: ' . $this->lastResponse);
      return false;

    } catch (\Exception $e) {
      $this->lastResponse = $e->getMessage();
      Log::error('SMTP Email API Exception: ' . $this->lastResponse);
      return false;
    }
    */
  }

  /**
   * Get standard email header
   */
  public static function getEmailHeader()
  {
    $logoUrl = url('images/email-header.png');

    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <!--[if IE]><html xmlns="http://www.w3.org/1999/xhtml" class="ie"><![endif]-->
            <!--[if !IE]><!-->
            <html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml">
            <!--<![endif]-->
            
            <head>
              <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
              <title></title>
              <!--[if !mso]><!-->
              <meta http-equiv="X-UA-Compatible" content="IE=edge" />
              <!--<![endif]-->
              <meta name="viewport" content="width=device-width" />
              <style type="text/css">
                a[href] {
                  font-size: 16px;
                  color: #15c;
              }
                @media only screen and (min-width: 620px) {
                  .wrapper {
                    min-width: 600px !important
                  }
                  .ipdc-btn{
                      border-radius: 4px;
                      display: inline-block;
                      font-size: 14px;
                      font-weight: bold;
                      line-height: 24px;
                      padding: 12px 24px;
                      text-align: center;
                      text-decoration: none !important;
                      color: #ffffff !important;
                      background-color: #00bde0;
                      font-family: Cabin, Avenir, sans-serif;
                  }
            
                  .wrapper h1 {}
            
                  .wrapper h1 {
                    font-size: 34px !important;
                    line-height: 43px !important
                  }
            
                  .wrapper h2 {}
            
                  .wrapper h2 {
                    font-size: 20px !important;
                    line-height: 28px !important
                  }
            
                  .wrapper h3 {}
            
                  .column {}
            
                  .wrapper .size-8 {
                    font-size: 8px !important;
                    line-height: 14px !important
                  }
            
                  .wrapper .size-9 {
                    font-size: 9px !important;
                    line-height: 16px !important
                  }
            
                  .wrapper .size-10 {
                    font-size: 10px !important;
                    line-height: 18px !important
                  }
            
                  .wrapper .size-11 {
                    font-size: 11px !important;
                    line-height: 19px !important
                  }
            
                  .wrapper .size-12 {
                    font-size: 12px !important;
                    line-height: 19px !important
                  }
            
                  .wrapper .size-13 {
                    font-size: 13px !important;
                    line-height: 21px !important
                  }
            
                  .wrapper .size-14 {
                    font-size: 14px !important;
                    line-height: 21px !important
                  }
            
                  .wrapper .size-15 {
                    font-size: 15px !important;
                    line-height: 23px !important
                  }
            
                  .wrapper .size-16 {
                    font-size: 16px !important;
                    line-height: 24px !important
                  }
            
                  .wrapper .size-17 {
                    font-size: 17px !important;
                    line-height: 26px !important
                  }
            
                  .wrapper .size-18 {
                    font-size: 18px !important;
                    line-height: 26px !important
                  }
            
                  .wrapper .size-20 {
                    font-size: 20px !important;
                    line-height: 28px !important
                  }
            
                  .wrapper .size-22 {
                    font-size: 22px !important;
                    line-height: 31px !important
                  }
            
                  .wrapper .size-24 {
                    font-size: 24px !important;
                    line-height: 32px !important
                  }
            
                  .wrapper .size-26 {
                    font-size: 26px !important;
                    line-height: 34px !important
                  }
            
                  .wrapper .size-28 {
                    font-size: 28px !important;
                    line-height: 36px !important
                  }
            
                  .wrapper .size-30 {
                    font-size: 30px !important;
                    line-height: 38px !important
                  }
            
                  .wrapper .size-32 {
                    font-size: 32px !important;
                    line-height: 40px !important
                  }
            
                  .wrapper .size-34 {
                    font-size: 34px !important;
                    line-height: 43px !important
                  }
            
                  .wrapper .size-36 {
                    font-size: 36px !important;
                    line-height: 43px !important
                  }
            
                  .wrapper .size-40 {
                    font-size: 40px !important;
                    line-height: 47px !important
                  }
            
                  .wrapper .size-44 {
                    font-size: 44px !important;
                    line-height: 50px !important
                  }
            
                  .wrapper .size-48 {
                    font-size: 48px !important;
                    line-height: 54px !important
                  }
            
                  .wrapper .size-56 {
                    font-size: 56px !important;
                    line-height: 60px !important
                  }
            
                  .wrapper .size-64 {
                    font-size: 64px !important;
                    line-height: 63px !important
                  }
                }
              </style>
              <meta name="x-apple-disable-message-reformatting" />
              <style type="text/css">
                .main,
                .mso {
                  margin: 0;
                  padding: 0;
                }
            
                table {
                  border-collapse: collapse;
                  table-layout: fixed;
                }
            
                * {
                  line-height: inherit;
                }
            
                [x-apple-data-detectors] {
                  color: inherit !important;
                  text-decoration: none !important;
                }
            
                .wrapper .footer__share-button a:hover,
                .wrapper .footer__share-button a:focus {
                  color: #ffffff !important;
                }
            
                .wrapper .footer__share-button a.icon-white:hover,
                .wrapper .footer__share-button a.icon-white:focus {
                  color: #ffffff !important;
                }
            
                .wrapper .footer__share-button a.icon-black:hover,
                .wrapper .footer__share-button a.icon-black:focus {
                  color: #000000 !important;
                }
            
                .btn a:hover,
                .btn a:focus,
                .footer__share-button a:hover,
                .footer__share-button a:focus,
                .email-footer__links a:hover,
                .email-footer__links a:focus {
                  opacity: 0.8;
                }
            
                .preheader,
                .header,
                .layout,
                .column {
                  transition: width 0.25s ease-in-out, max-width 0.25s ease-in-out;
                }
            
                .preheader td {
                  padding-bottom: 8px;
                }
            
                .layout,
                div.header {
                  max-width: 400px !important;
                  -fallback-width: 95% !important;
                  width: calc(100% - 20px) !important;
                }
            
                div.preheader {
                  max-width: 360px !important;
                  -fallback-width: 90% !important;
                  width: calc(100% - 60px) !important;
                }
            
                .snippet,
                .webversion {
                  Float: none !important;
                }
            
                .stack .column {
                  max-width: 400px !important;
                  width: 100% !important;
                }
            
                .fixed-width.has-border {
                  max-width: 402px !important;
                }
            
                .fixed-width.has-border .layout__inner {
                  box-sizing: border-box;
                }
            
                .snippet,
                .webversion {
                  width: 50% !important;
                }
            
                .ie .btn {
                  width: 100%;
                }
            
                .ie .stack .column,
                .ie .stack .gutter {
                  display: table-cell;
                  float: none !important;
                }
            
                .ie div.preheader,
                .ie .email-footer {
                  max-width: 560px !important;
                  width: 560px !important;
                }
            
                .ie .snippet,
                .ie .webversion {
                  width: 280px !important;
                }
            
                .ie div.header,
                .ie .layout {
                  max-width: 600px !important;
                  width: 600px !important;
                }
            
                .ie .two-col .column {
                  max-width: 300px !important;
                  width: 300px !important;
                }
            
                .ie .three-col .column,
                .ie .narrow {
                  max-width: 200px !important;
                  width: 200px !important;
                }
            
                .ie .wide {
                  width: 400px !important;
                }
            
                .ie .stack.fixed-width.has-border,
                .ie .stack.has-gutter.has-border {
                  max-width: 602px !important;
                  width: 602px !important;
                }
            
                .ie .stack.two-col.has-gutter .column {
                  max-width: 290px !important;
                  width: 290px !important;
                }
            
                .ie .stack.three-col.has-gutter .column,
                .ie .stack.has-gutter .narrow {
                  max-width: 188px !important;
                  width: 188px !important;
                }
            
                .ie .stack.has-gutter .wide {
                  max-width: 394px !important;
                  width: 394px !important;
                }
            
                .ie .stack.two-col.has-gutter.has-border .column {
                  max-width: 292px !important;
                  width: 292px !important;
                }
            
                .ie .stack.three-col.has-gutter.has-border .column,
                .ie .stack.has-gutter.has-border .narrow {
                  max-width: 190px !important;
                  width: 190px !important;
                }
            
                .ie .stack.has-gutter.has-border .wide {
                  max-width: 396px !important;
                  width: 396px !important;
                }
            
                .ie .fixed-width .layout__inner {
                  border-left: 0 none white !important;
                  border-right: 0 none white !important;
                }
            
                .ie .layout__edges {
                  display: none;
                }
            
                .mso .layout__edges {
                  font-size: 0;
                }
            
                .layout-fixed-width,
                .mso .layout-full-width {
                  background-color: #ffffff;
                }
            
                @media only screen and (min-width: 620px) {
            
                  .column,
                  .gutter {
                    display: table-cell;
                    Float: none !important;
                    vertical-align: top;
                  }
            
                  div.preheader,
                  .email-footer {
                    max-width: 560px !important;
                    width: 560px !important;
                  }
            
                  .snippet,
                  .webversion {
                    width: 280px !important;
                  }
            
                  div.header,
                  .layout,
                  .one-col .column {
                    max-width: 600px !important;
                    width: 600px !important;
                  }
            
                  .fixed-width.has-border,
                  .fixed-width.x_has-border,
                  .has-gutter.has-border,
                  .has-gutter.x_has-border {
                    max-width: 602px !important;
                    width: 602px !important;
                  }
            
                  .two-col .column {
                    max-width: 300px !important;
                    width: 300px !important;
                  }
            
                  .three-col .column,
                  .column.narrow,
                  .column.x_narrow {
                    max-width: 200px !important;
                    width: 200px !important;
                  }
            
                  .column.wide,
                  .column.x_wide {
                    width: 400px !important;
                  }
            
                  .two-col.has-gutter .column,
                  .two-col.x_has-gutter .column {
                    max-width: 290px !important;
                    width: 290px !important;
                  }
            
                  .three-col.has-gutter .column,
                  .three-col.x_has-gutter .column,
                  .has-gutter .narrow {
                    max-width: 188px !important;
                    width: 188px !important;
                  }
            
                  .has-gutter .wide {
                    max-width: 394px !important;
                    width: 394px !important;
                  }
            
                  .two-col.has-gutter.has-border .column,
                  .two-col.x_has-gutter.x_has-border .column {
                    max-width: 292px !important;
                    width: 292px !important;
                  }
            
                  .three-col.has-gutter.has-border .column,
                  .three-col.x_has-gutter.x_has-border .column,
                  .has-gutter.has-border .narrow,
                  .has-gutter.x_has-border .narrow {
                    max-width: 190px !important;
                    width: 190px !important;
                  }
            
                  .has-gutter.has-border .wide,
                  .has-gutter.x_has-border .wide {
                    max-width: 396px !important;
                    width: 396px !important;
                  }
                }
            
                @supports (display: flex) {
                  @media only screen and (min-width: 620px) {
                    .fixed-width.has-border .layout__inner {
                      display: flex !important;
                    }
                  }
                }
            
                /***
            * Mobile Styles
            *
            * 1. Overriding inline styles
            */
                @media(max-width: 619px) {
            
                  .email-flexible-footer .left-aligned-footer .column,
                  .email-flexible-footer .center-aligned-footer,
                  .email-flexible-footer .right-aligned-footer .column {
                    max-width: 100% !important;
                    /* [1] */
                    text-align: center !important;
                    /* [1] */
                    width: 100% !important;
                    /* [1] */
                  }
            
                  .flexible-footer-logo {
                    margin-left: 0px !important;
                    /* [1] */
                    margin-right: 0px !important;
                    /* [1] */
                  }
            
                  .email-flexible-footer .left-aligned-footer .flexible-footer__share-button__container,
                  .email-flexible-footer .center-aligned-footer .flexible-footer__share-button__container,
                  .email-flexible-footer .right-aligned-footer .flexible-footer__share-button__container {
                    display: inline-block;
                    margin-left: 5px !important;
                    /* [1] */
                    margin-right: 5px !important;
                    /* [1] */
                  }
            
                  .email-flexible-footer__additionalinfo--center {
                    text-align: center !important;
                    /* [1] */
                  }
            
                  .email-flexible-footer .left-aligned-footer table,
                  .email-flexible-footer .center-aligned-footer table,
                  .email-flexible-footer .right-aligned-footer table {
                    display: table !important;
                    /* [1] */
                    width: 100% !important;
                    /* [1] */
                  }
            
                  .email-flexible-footer .footer__share-button,
                  .email-flexible-footer .email-footer__additional-info {
                    margin-left: 20px;
                    margin-right: 20px;
                  }
                }
            
                @media (max-width: 321px) {
                  .fixed-width.has-border .layout__inner {
                    border-width: 1px 0 !important;
                  }
            
                  .layout,
                  .stack .column {
                    min-width: 320px !important;
                    width: 320px !important;
                  }
            
                  .border {
                    display: none;
                  }
            
                  .has-gutter .border {
                    display: table-cell;
                  }
                }
            
                .mso div {
                  border: 0 none white !important;
                }
            
                .mso .w560 .divider {
                  Margin-left: 260px !important;
                  Margin-right: 260px !important;
                }
            
                .mso .w360 .divider {
                  Margin-left: 160px !important;
                  Margin-right: 160px !important;
                }
            
                .mso .w260 .divider {
                  Margin-left: 110px !important;
                  Margin-right: 110px !important;
                }
            
                .mso .w160 .divider {
                  Margin-left: 60px !important;
                  Margin-right: 60px !important;
                }
            
                .mso .w354 .divider {
                  Margin-left: 157px !important;
                  Margin-right: 157px !important;
                }
            
                .mso .w250 .divider {
                  Margin-left: 105px !important;
                  Margin-right: 105px !important;
                }
            
                .mso .w148 .divider {
                  Margin-left: 54px !important;
                  Margin-right: 54px !important;
                }
            
                .mso .size-8,
                .ie .size-8 {
                  font-size: 8px !important;
                  line-height: 14px !important;
                }
            
                .mso .size-9,
                .ie .size-9 {
                  font-size: 9px !important;
                  line-height: 16px !important;
                }
            
                .mso .size-10,
                .ie .size-10 {
                  font-size: 10px !important;
                  line-height: 18px !important;
                }
            
                .mso .size-11,
                .ie .size-11 {
                  font-size: 11px !important;
                  line-height: 19px !important;
                }
            
                .mso .size-12,
                .ie .size-12 {
                  font-size: 12px !important;
                  line-height: 19px !important;
                }
            
                .mso .size-13,
                .ie .size-13 {
                  font-size: 13px !important;
                  line-height: 21px !important;
                }
            
                .mso .size-14,
                .ie .size-14 {
                  font-size: 14px !important;
                  line-height: 21px !important;
                }
            
                .mso .size-15,
                .ie .size-15 {
                  font-size: 15px !important;
                  line-height: 23px !important;
                }
            
                .mso .size-16,
                .ie .size-16 {
                  font-size: 16px !important;
                  line-height: 24px !important;
                }
            
                .mso .size-17,
                .ie .size-17 {
                  font-size: 17px !important;
                  line-height: 26px !important;
                }
            
                .mso .size-18,
                .ie .size-18 {
                  font-size: 18px !important;
                  line-height: 26px !important;
                }
            
                .mso .size-20,
                .ie .size-20 {
                  font-size: 20px !important;
                  line-height: 28px !important;
                }
            
                .mso .size-22,
                .ie .size-22 {
                  font-size: 22px !important;
                  line-height: 31px !important;
                }
            
                .mso .size-24,
                .ie .size-24 {
                  font-size: 24px !important;
                  line-height: 32px !important;
                }
            
                .mso .size-26,
                .ie .size-26 {
                  font-size: 26px !important;
                  line-height: 34px !important;
                }
            
                .mso .size-28,
                .ie .size-28 {
                  font-size: 28px !important;
                  line-height: 36px !important;
                }
            
                .mso .size-30,
                .ie .size-30 {
                  font-size: 30px !important;
                  line-height: 38px !important;
                }
            
                .mso .size-32,
                .ie .size-32 {
                  font-size: 32px !important;
                  line-height: 40px !important;
                }
            
                .mso .size-34,
                .ie .size-34 {
                  font-size: 34px !important;
                  line-height: 43px !important;
                }
            
                .mso .size-36,
                .ie .size-36 {
                  font-size: 36px !important;
                  line-height: 43px !important;
                }
            
                .mso .size-40,
                .ie .size-40 {
                  font-size: 40px !important;
                  line-height: 47px !important;
                }
            
                .mso .size-44,
                .ie .size-44 {
                  font-size: 44px !important;
                  line-height: 50px !important;
                }
            
                .mso .size-48,
                .ie .size-48 {
                  font-size: 48px !important;
                  line-height: 54px !important;
                }
            
                .mso .size-56,
                .ie .size-56 {
                  font-size: 56px !important;
                  line-height: 60px !important;
                }
            
                .mso .size-64,
                .ie .size-64 {
                  font-size: 64px !important;
                  line-height: 63px !important;
                }
              </style>
            
              <!--[if !mso]><!-->
              <style type="text/css">
                @import url(https://fonts.googleapis.com/css?family=Cabin:400,700,400italic,700italic|Open+Sans:400italic,700italic,700,400);
              </style>
              <link href="https://fonts.googleapis.com/css?family=Cabin:400,700,400italic,700italic|Open+Sans:400italic,700italic,700,400" rel="stylesheet" type="text/css" />
              <!--<![endif]-->
              <style type="text/css">
                .main,
                .mso {
                  background-color: #e7f5f9
                }
            
                .logo a:hover,
                .logo a:focus {
                  color: #1e2e3b !important
                }
            
                .footer-logo a:hover,
                .footer-logo a:focus {
                  color: #372d1b !important
                }
            
                .mso .layout-has-border {
                  border-top: 1px solid #95d3e5;
                  border-bottom: 1px solid #95d3e5
                }
            
                .mso .layout-has-bottom-border {
                  border-bottom: 1px solid #95d3e5
                }
            
                .mso .border,
                .ie .border {
                  background-color: #95d3e5
                }
            
                .mso h1,
                .ie h1 {}
            
                .mso h1,
                .ie h1 {
                  font-size: 34px !important;
                  line-height: 43px !important
                }
            
                .mso h2,
                .ie h2 {}
            
                .mso h2,
                .ie h2 {
                  font-size: 20px !important;
                  line-height: 28px !important
                }
            
                .mso h3,
                .ie h3 {}
            
                .mso .layout__inner,
                .ie .layout__inner {}
            
                .mso .footer__share-button p {}
            
                .mso .footer__share-button p {
                  font-family: Cabin, Avenir, sans-serif
                }
                .ipdc-btn{
                      border-radius: 4px;
                      display: inline-block !important;
                      font-size: 14px;
                      font-weight: bold;
                      line-height: 24px;
                      padding: 12px 24px;
                      text-align: center;
                      text-decoration: none !important;
                      color: #ffffff !important;
                      background-color: #00bde0 !important;
                      font-family: Cabin, Avenir, sans-serif;
                  }
              </style>
              <meta name="robots" content="noindex,nofollow" />
              <meta property="og:title" content="My First Campaign" />
            </head>
            <!--[if mso]>
              <body class="mso">
            <![endif]-->
            <!--[if !mso]><!-->
            
            
            
            
            
            <body class="main full-padding" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;">
              <!--<![endif]-->
              <table class="wrapper" style="border-collapse: collapse;table-layout: fixed;min-width: 320px;width: 100%;background-color: #e7f5f9;" cellpadding="0" cellspacing="0" role="presentation">
                <tbody>
                  <tr>
                    <td>
                      <div role="banner">
                        <div class="preheader" style="Margin: 0 auto;max-width: 560px;min-width: 280px; width: 280px;width: calc(28000% - 167440px);">
                          <div style="border-collapse: collapse;display: table;width: 100%;">
                            <!--[if (mso)|(IE)]><table align="center" class="preheader" cellpadding="0" cellspacing="0" role="presentation"><tr><td style="width: 280px" valign="top"><![endif]-->
                            <div class="snippet" style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 140px; width: 140px;width: calc(14000% - 78120px);padding: 10px 0 5px 0;color: #93a6ad;font-family: Cabin,Avenir,sans-serif;">
            
                            </div>
                            <!--[if (mso)|(IE)]></td><td style="width: 280px" valign="top"><![endif]-->
                            <!-- <div class="webversion" style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 139px; width: 139px;width: calc(14100% - 78680px);padding: 10px 0 5px 0;text-align: right;color: #93a6ad;font-family: Cabin,Avenir,sans-serif;">
                    <p style="Margin-top: 0;Margin-bottom: 0;" emb-social="webversion">No images? <webversion style="text-decoration: underline;">Click here</webversion></p>
                  </div> -->
                            <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                          </div>
                        </div>
            
                      </div>
                      <div>
                        <div class="layout one-col fixed-width stack" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background:#003860;text-align: center;">
                          <img src="' . $logoUrl . '" alt="Pramukh Saarthi Logo" style="width: 100%;display: block;padding: 40px 0;">
                          <div style="background-color: #00A6D4;padding: 1px;background-image: linear-gradient(to top right, #00A6D4, #1DCFF2 80%);"></div>
                        </div>
                        <div class="layout one-col fixed-width stack" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                          <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;background-color: #ffffff;">
                            <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-fixed-width" style="background-color: #ffffff;"><td style="width: 600px" class="w560"><![endif]-->
                            <div class="column" style="text-align: left;color: #8e8e8e;font-size: 14px;line-height: 21px;font-family: Cabin,Avenir,sans-serif;">
            
                              <div style="Margin-left: 20px;Margin-right: 20px;">
                                <div style="mso-line-height-rule: exactly;mso-text-raise: 11px;vertical-align: middle;">';
  }

  public static function getEmailFooter()
  {
    return '
            </div>
                </div>
                <br />
                <div role="contentinfo">
                  <div style="line-height:4px;font-size:4px;" id="footer-top-spacing">&nbsp;</div>
                  <div class="layout email-flexible-footer email-footer" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;" id="footer-content">
                    <div class="layout__inner center-aligned-footer" style="border-collapse: collapse;display: table;width: 100%;">
                      <!--[if (mso)|(IE)]><table style="width: 600px;" align="center" cellpadding="0" cellspacing="0" role="presentation"><![endif]-->
                      <!--[if (mso)|(IE)]><tr class="layout-email-footer"><![endif]-->
                      <div class="column" style="text-align: center;font-size: 12px;line-height: 19px;color: #93a6ad;font-family: Cabin,Avenir,sans-serif;display: none;" align="center">
                        <div class="footer-logo emb-logo-margin-box" style="font-size: 26px;line-height: 32px;Margin-top: 10px;Margin-bottom: 20px;color: #7b663d;font-family: Roboto,Tahoma,sans-serif;" align="center">
                          <div emb-flexible-footer-logo align="center"></div>
                        </div>
                      </div>
                      <!--[if (mso)|(IE)]></tr><![endif]-->
                      <!--[if (mso)|(IE)]><tr class="layout-email-footer"><![endif]-->
                      <div class="email-footer__additional-info" style="font-size: 12px;line-height: 19px;margin-bottom: 15px;text-align: center;">
            							<div class="email-footer__additional-info" style="font-size: 16px;line-height: 19px;margin-bottom: 15px;">
            							<span><a href="mailto:sankalp@pramukh.academy"><preferences style="color: #93a6ad;text-decoration: underline"><strong>sankalp@pramukh.academy</strong></preferences></a></span>
            							<span>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="tel:+919510060898"><preferences style="color: #93a6ad;text-decoration: underline"><strong>+91 951 006 0898</strong></preferences></a></span>
            							<br/><br/><span><a href="https://pramukh.academy/pramukh-sankalp-programme/"><preferences style="color: #93a6ad;text-decoration: underline"><strong>www.pramukh.academy/pramukh-sankalp-programme</strong></preferences></a></span>
            						  </div><br/>
            						  
            							</div>
            				
            						  <div class="column" style="text-align: center;font-size: 12px;line-height: 19px;color: #93a6ad;font-family: Cabin,Avenir,sans-serif;display: inline;" align="center">
            					  <div style="margin-left: 0;margin-right: 0;Margin-top: 10px;Margin-bottom: 10px;">
            						<div class="footer__share-button">
            						<table style="border-collapse: collapse;table-layout: fixed;display: inline;" cellpadding="0" cellspacing="0">
            							<tbody>
            								<tr>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://www.facebook.com/pramukhsankalp"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/facebook.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://twitter.com/pramukhacademy"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/twitter.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://www.instagram.com/pramukhsankalp?igsh=dzRqcjRxNGV2cnJt"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/instagram.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://www.youtube.com/@pramukhsankalp?si=3Iqx0g3t-RxaTKGG"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/youtube.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            								</tr>
            							</tbody>
            						</table><br/>
            						<table style="border-collapse: collapse;table-layout: fixed;display: inline;" cellpadding="0" cellspacing="0">
            							<tbody>
            								<tr>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://www.linkedin.com/in/sankalp-programme-570bb6397/"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/linkedin.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://t.me/pramukhsankalp"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/telegram.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            									<td class="flexible-footer_share-button_container" style="margin-bottom: 10px;mso-line-height-rule: exactly;display: inline-block;margin-left: 5px;margin-right: 5px;">
            										<a href="https://www.whatsapp.com/channel/0029VbBRHA2AYlUO5q72z604"><img style="border: 0;display: inline-block;margin-right: 2px;vertical-align: -3px;" src="https://connect.ipdc.org/assets/email/whatsapp.png" width="30" height="30" border="0" alt="" /></a>
            									</td>
            								</tr>
            							</tbody>
            						</table>						
            					  </div>
            					  </div>
            					</div>
                      <!--[if (mso)|(IE)]></tr><![endif]-->
                      <!--[if (mso)|(IE)]><tr class="layout-email-footer"><![endif]-->
                      <table style="border-collapse: collapse;table-layout: fixed;width: 100%;" cellpadding="0" cellspacing="0">
                        <tbody>
                          <tr>
                            <td>
                              <div class="column js-footer-additional-info" style="text-align: center;font-size: 12px;line-height: 19px;color: #93a6ad;font-family: Cabin,Avenir,sans-serif;display: inline;width: 100%;" align="center">
                                <div style="margin-left: 0;margin-right: 0;Margin-top: 10px;Margin-bottom: 10px;">
                                  <div class="email-footer__additional-info" style="font-size: 12px;line-height: 19px;margin-bottom: 15px;">
                                    <span>
                                      <preferences lang="en">&copy; PRAMUKH Academy.</preferences>
                                    </span>
                                  </div>
                                  <!--[if mso]>&nbsp;<![endif]-->
                                </div>
                              </div>
                    </div>
                    </td>
                    </tr>
                    </tbody>
                    </table>
                    <!--[if (mso)|(IE)]></tr></table><![endif]-->
                  </div>
                </div>
                <div style="line-height:40px;font-size:40px;" id="footer-bottom-spacing">&nbsp;</div>
                </div>
            
                </td>
                </tr>
                </tbody>
                </table>
                <style type="text/css">
                  @media (max-width:619px) {
            
                    .email-flexible-footer .left-aligned-footer .column,
                    .email-flexible-footer .center-aligned-footer,
                    .email-flexible-footer .right-aligned-footer .column {
                      max-width: 100% !important;
                      text-align: center !important;
                      width: 100% !important
                    }
            
                    .flexible-footer-logo {
                      margin-left: 0px !important;
                      margin-right: 0px !important
                    }
            
                    .email-flexible-footer .left-aligned-footer .flexible-footer__share-button__container,
                    .email-flexible-footer .center-aligned-footer .flexible-footer__share-button__container,
                    .email-flexible-footer .right-aligned-footer .flexible-footer__share-button__container {
                      display: inline-block;
                      margin-left: 5px !important;
                      margin-right: 5px !important
                    }
            
                    .email-flexible-footer__additionalinfo--center {
                      text-align: center !important
                    }
            
                    .email-flexible-footer .left-aligned-footer table,
                    .email-flexible-footer .center-aligned-footer table,
                    .email-flexible-footer .right-aligned-footer table {
                      display: table !important;
                      width: 100% !important
                    }
            
                    .email-flexible-footer .footer__share-button,
                    .email-flexible-footer .email-footer__additional-info {
                      margin-left: 20px;
                      margin-right: 20px
                    }
                  }
                </style>
                </body>
                </html>';
  }
}
