Rule ID: {{ $rule_id }}<br/>
Start Date: {{ $start_date }}<br/>
End Date: {{ $end_date }}<br/>
Passed: {{ $passed }}<br/>
DATA:<br/>
@foreach($data['info'] as $group)
&nbsp;&nbsp;&nbsp;&nbsp;Group: {{ $group['group'] }}<br/>
&nbsp;&nbsp;&nbsp;&nbsp;Items:<br/>
@foreach($group['items'] as $item)
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rule Condition Type: {{ $item['ruleConditionType'] }}<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;First Value: {{ $item['first_value'] }}<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Second Value: {{ $item['second_value'] }}<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Operation: {{ $item['operation'] }}<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Passed: {{ $item['passed'] }}<br/>
@endforeach
@endforeach
EFFECT IF EXECUTE:<br/>
@if (isset($data['visual-effect']))
    @foreach($data['visual-effect'] as $key => $effect)
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $key }}: {{ is_array($effect) || is_object($effect) ? json_encode($effect) : $effect }}<br/>
    @endforeach
@endif
@if (isset($data['effect']))
    @foreach($data['effect'] as $key => $effect)
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $key }}: {{ is_array($effect) || is_object($effect) ? json_encode($effect) : $effect }}<br/>
    @endforeach
@endif
