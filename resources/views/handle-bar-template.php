<script id="work-log-entries" type="text/x-handlebars-template">
    <br/>
    <br/>
    {{#each worklogs}}
    <div class="time-entry">
            <span>{{created}}</span>
            <span>{{timeSpent}}</span>
            <span>{{comment}}</span>
    </div>
    {{/each}}
    <br/>
</script>

<script id="pre-work-log-entries" type="text/x-handlebars-template">
{{#each worklogs}}{{created}} - {{timeSpent}} - {{comment}}<?php echo "\n"; ?>{{/each}}
</script>