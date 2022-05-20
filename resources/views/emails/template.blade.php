<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <!--[if gte mso 9]><xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml><![endif]-->
    <!-- fix outlook zooming on 120 DPI windows devices -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- So that mobile will display zoomed in -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- enable media queries for windows phone 8 -->
    <meta name="format-detection" content="date=no"> <!-- disable auto date linking in iOS 7-9 -->
    <meta name="format-detection" content="telephone=no"> <!-- disable auto telephone linking in iOS 7-9 -->
    <title>Point RED</title>
    <style type="text/css">
        .header,
        .title,
        .subtitle,
        .footer-text {
            font-family: Helvetica, Roboto, sans-serif;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            padding-bottom: 12px;
            color: #DF4726;
        }

        .footer-text {
            font-size: 12px;
            line-height: 16px;
            color: #aaaaaa;
        }
        .footer-text a {
            color: #aaaaaa;
        }

        .container {
            width: 600px;
            max-width: 600px;
        }

        .container-padding {
            padding-left: 24px;
            padding-right: 24px;
        }

        .content {
            padding-top: 12px;
            padding-bottom: 12px;
            background-color: #ffffff;
        }

        code {
            background-color: #eee;
            padding: 0 4px;
            font-family: Menlo, Courier, monospace;
            font-size: 12px;
        }

        hr {
            border: 0;
            border-bottom: 1px solid #cccccc;
        }

        .hr {
            height: 1px;
            border-bottom: 1px solid #cccccc;
        }

        .title {
            font-size: 18px;
            font-weight: 600;
            color: #374550;
        }

        .subtitle {
            font-size: 16px;
            font-weight: 600;
            color: #2469A0;
        }
        .subtitle span {
            font-weight: 400;
            color: #999999;
        }

        .body-text {
            font-family: Helvetica, Roboto, sans-serif;
            font-size: 14px;
            line-height: 20px;
            text-align: left;
            color: #333333;
        }

        a[href^="x-apple-data-detectors:"],
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        body {
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table {
            border-spacing: 0;
        }

        table td {
            border-collapse: collapse;
        }

        .ExternalClass {
            width: 100%;
        }

        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }

        .ReadMsgBody {
            width: 100%;
            background-color: #ebebeb;
        }

        table {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        .yshortcuts a {
            border-bottom: none !important;
        }

        @media screen and (max-width: 599px) {
            .force-row,
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        @media screen and (max-width: 400px) {
            .container-padding {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }
        .ios-footer a {
            color: #aaaaaa !important;
            text-decoration: underline;
        }
    </style>
    @yield('css')
</head>
<body style="margin:0; padding:0;" bgcolor="#F0F0F0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<!-- 100% background wrapper (grey background) -->
<table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0">
    <tr>
        <td align="center" valign="top" bgcolor="#F0F0F0" style="background-color: #F0F0F0;">

            <br>

            <!-- 600px container (white background) -->
            <table border="0" width="600" cellpadding="0" cellspacing="0" class="container">
                <tr>
                    <td class="container-padding header" align="center">
                        <a href="#" target="_blank" style="text-decoration:none;"><img src="{{ url('/img/logo.png') }}" height="50" style="display:block; font-size:15px; color:#30373b;  font-weight:bold;" border="0" alt="" /></a>
                    </td>
                </tr>
                <tr>
                    <td class="container-padding content" align="left">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td class="container-padding footer-text" align="center">
                        <br>
                        This is an automated message, please do not reply directly to this email.
                        <br>
                        Copyright &copy; 2020 RED Point. All Rights Reserved.
                        <br>
                        <a href="https://www.point.red">www.point.red</a><br>
                    </td>
                </tr>
            </table><!--/600px container -->
        </td>
    </tr>
</table><!--/100% background wrapper-->
</body>
</html>
