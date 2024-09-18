<x-mail::message>
# {{ $user->name }} has requested for a vendor account

<x-mail::panel>
    He/She has provided a url link to their sales page, take a look at
    their sales page and review it. The button below; once clicked takes you to it!
</x-mail::panel>

Here's the user email for a reply back if needed: {{ $user->email }}

<x-mail::button :url="$saleurl">
Open Sales page
</x-mail::button>

<button><a href="https://link-to-verify?user={{ $user->name }}&id={{ $user->id }}">Verify Vendor</a></button>

From,<br>
{{ config('app.name') }}
</x-mail::message>