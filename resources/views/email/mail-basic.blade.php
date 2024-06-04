@extends('email.basic')


@section('content')


    @isset($message_first)
        <p class="body__text1"
           style="
                margin-top: 0px;
                text-align: left;
                font-size: 16px;
                margin-bottom: 30px;
            "
        >
            {!! $message_first !!}
        </p>
    @endisset


    @isset($message_second)
        <p  class="body__text1"
            style="
                margin-top: 0px;
                text-align: left;
                font-size: 16px;
                margin-bottom: 30px;
			"
        >
            {{$message_second}}

        </p>
    @endisset


    @isset($message_three)
        <p  class="body__text2 body__light"
            style="
                margin-top: 0px;
                margin-bottom: 0px;
                text-align: left;
                font-size: 14px;
                color: #9f9da5;
            "
        >
            {{$message_three}}
        </p>
    @endisset


    @isset($support_text)
        <p  class="body__text2 body__light"
            style="
                margin-top: 0px;
                margin-bottom: 0px;
                text-align: left;
                font-size: 14px;
                color: #9f9da5;
            "
        >
            {{$support_text}}
            <a href="mailto:{{$support_mail}}" target="_blank" style="color: #686670;font-weight: 500;">
                {{$support_mail}}
            </a>
        </p>
    @endisset


@endsection
