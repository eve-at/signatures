@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h1>Legal</h1>

                <h2 id="ccp">CCP</h2>
                <p>EVE Online and the EVE logo are the registered trademarks of CCP hf. All rights are reserved worldwide. All other trademarks are the property of their respective owners. EVE Online, the EVE logo, EVE and all associated logos and designs are the intellectual property of CCP hf. All artwork, screenshots, characters, vehicles, storylines, world facts or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. CCP hf. does not endorse, and is not in any way affiliated with {{ config('app.name', 'EVE Wormhole Signatures') }}. CCP is in no way responsible for the content on or functioning of this website, nor can it be liable for any damage arising from the use of this website.</p>

                <h2 id="general-rules">General Rules</h2>
                <ul>
                    <li>Failure to comply with any of the regulations in this subcategory will result in the immediate termination of your service;</li>
                    <li>Any intentional action that by any means interrupts the service of {{ config('app.name', 'EVE Wormhole Signatures') }} is prohibited;</li>
                    <li>Accessing the API for the purpose of causing increased load on the server is not allowed, and will result in an IP ban from the service;</li>
                    <li>Placing commercial advertisements, be it links, images or any other form, into any part of the site, is not allowed.</li>
                </ul>

                <h2 id="obligations">Obligations</h2>
                <ul>
                    <li>Items listed in this section are guaranteed by {{ config('app.name', 'EVE Wormhole Signatures') }} as a term of service;</li>
                    <li>Under no circumstances is a refund guaranteed by {{ config('app.name', 'EVE Wormhole Signatures') }}. Refunds, however, may be administered at the discretion of the {{ config('app.name', 'EVE Wormhole Signatures') }} administrators;</li>
                    <li>{{ config('app.name', 'EVE Wormhole Signatures') }} will not release API keys, emails or passwords to the public, or anyone else without prior consent of the account holder.</li>
                </ul>

                <h2 id="cookies">Cookies</h2>
                <p>{{ config('app.name', 'EVE Wormhole Signatures') }} use cookies for essential purposes. Such as analytics. {{ config('app.name', 'EVE Wormhole Signatures') }}. will never use the cookie data for targetted advertising or similar stuff.</p>

                <strong>PHPSESSID</strong>
                <p>Is used by our session_id system</p>

                <strong>utma utmb utmc utmz</strong>
                <p>These cookies are set by Google Analytics, and tell us how many people visit the website, how they arrived at the site, what pages they visit, what web browsers they are using to view pages etc. They cannot identify you personally.</p>
            </div>
        </div>
    </div>
@endsection