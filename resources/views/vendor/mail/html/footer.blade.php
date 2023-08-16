<tr>
<td>
<table class="footer content-cell" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
@isset($links)
<tr>
<td align="center">
{{Illuminate\Mail\Markdown::parse($links)}}
</td>
</tr>
@endisset
<tr>
<td align="center">
{{ Illuminate\Mail\Markdown::parse('sent with 💙 from '. config('app.name')) }}
</td>
</tr>
@isset($copyright)
<tr>
<td align="center">
{{ Illuminate\Mail\Markdown::parse($copyright) }}
</td>
</tr>
@endisset
</table>
</td>
</tr>
