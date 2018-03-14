<div class="sequence-navigator">
<% if $HasSequence %>
        <h3>current data-entry wizard</h3>
    <% with $CurrentSequence %>
        <p class="intro-sequence">
            <a title="$Description.ATT">$Title</a>
            <% if $TotalNumberOfPages %><span class="steps-in-sequence">Step $CurrentRecordPositionInSequence / $TotalNumberOfPages.</span><% end_if %>
        </p>
    <% end_with %>
    <% if canGoPreviousOrNextPage %>
        <p class="prev-next-for-sequence">
            <% if canGoPreviousPage %><a href="$PreviousSequenceLink" class="previous special-action">$PreviousPageObject.Title</a><% end_if %>
            <% if canGoNextPage %><a href="$NextSequenceLink" class="next special-action">$NextPageObject.Title</a><% end_if %>
        </p>
    <% end_if %>
    <% if $AllPages %>
        <ul>
        <% loop $AllPages %><li class="$SequenceLinkingMode"><% if $exists %>&laquo; <a href="$FrontEndEditLink">$SequenceTitle</a><% else %>&raquo; $SequenceTitle</li><% end_if %><% end_loop %>
        </ul>
    <% end_if %>
    <p class="quit-sequence">
        <a href="$StopSequenceLink" class="special-action">Quit Sequence Editing</a>
    </p>
<% else %>
    <% if $ListOfSequences %>
    <div class="list-of-sequences">
        <h3>Start a data-entry wizard:</h3>
        <ul>
            <% loop $ListOfSequences %><li><a href="$Link" title="$Description.ATT" class="externalLink">$Title</a></li><% end_loop %>
        </ul>
    </div>
    <% end_if %>
<% end_if %>
</div>
