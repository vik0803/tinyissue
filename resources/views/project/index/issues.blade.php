@if ($issues)
<ul class="issues">
    @foreach ($issues as $issue)
    <li>
        <a href="" class="comments">{{ $issue->count_comments }}</a>
        <a href="" class="id">#{{ $issue->id }}</a>
        <div class="data">
                <a href="{{ $issue->to() }}">{{ $issue->title }}</a>
                <div class="info">
                        @lang('tinyissue.created_by')
                        <strong>{{ $issue->user->firstname . ' ' . $issue->user->lastname }}</strong>
                        {{ Html::age($issue->created_at) }}

                        @if($issue->updated_by)
                        - @lang('tinyissue.updated_by') <strong>{{ $issue->updatedBy->firstname . ' ' . $issue->updatedBy->lastname }}</strong>
                        {{ Html::age($issue->updated_at) }}
                        @endif
                </div>
        </div>
    </li>
    @endforeach
</ul>
@else
<p>@lang('tinyissue.no_issues')</p>
@endif
