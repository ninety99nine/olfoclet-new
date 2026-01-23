@component('mail::message')

<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('logo/swirl-text.png') }}" alt="{{ config('app.name') }} Logo" style="max-width: 150px; height: auto;" />
</div>

# You’ve Been Invited to Join {{ $appName }}!

Hello {{ $firstName ?: 'there' }},

You’ve been invited to join the team at **{{ $appName }}** on {{ config('app.name') }}. To get started, please verify your email address to activate your account.

**Email:** {{ $email }}

@component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
Verify Email Address
@endcomponent

If you did not expect this invitation, please ignore this email or contact our support team.

Thanks,
<br>
{{ config('app.name') }}

@endcomponent
