@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
<table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto;">
<tr>
<td style="padding: 0 12px;">
<span style="color: #c4b49a; font-size: 24px; line-height: 1;">●</span>
</td>
<td>
<span style="font-family: Georgia, 'Times New Roman', serif; font-size: 20px; color: #f4f0e8; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 400;">
{!! $slot !!}
</span>
</td>
<td style="padding: 0 12px;">
<span style="color: #c4b49a; font-size: 24px; line-height: 1;">●</span>
</td>
</tr>
</table>
@endif
</a>
</td>
</tr>
