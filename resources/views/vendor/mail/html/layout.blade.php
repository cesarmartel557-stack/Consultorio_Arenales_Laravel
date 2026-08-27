<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
@media only screen and (max-width: 600px) {
  .inner-body, .footer, .content {
    width: 100% !important;
  }
  .content-cell {
    padding: 24px 18px !important;
  }
}
@media only screen and (max-width: 500px) {
  .button {
    width: 100% !important;
    text-align: center !important;
  }
}
</style>
{!! $head ?? '' !!}
</head>
<body style="background-color: #edf4f6; margin: 0; padding: 24px 0; font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #edf4f6; width: 100%; margin: 0; padding: 0;">
<tr>
<td align="center" style="padding: 10px 10px 30px 10px;">
<table class="content" width="580" cellpadding="0" cellspacing="0" role="presentation" style="width: 580px; max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; box-shadow: 0 10px 30px rgba(18,85,107,0.08); border: 1px solid #dfe9ec; overflow: hidden;">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: hidden !important;">
<table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; background-color: #ffffff;">
<!-- Body content -->
<tr>
<td class="content-cell" style="padding: 34px 36px 28px 36px; font-family: 'Montserrat', Arial, sans-serif; color: #33454d;">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
