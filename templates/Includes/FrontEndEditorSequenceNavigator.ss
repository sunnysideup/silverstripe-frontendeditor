<div class="sequence-navigator">
<% if $HasSequence %>
        <h3>current data-entry wizard</h3>
    <% with $CurrentSequence %>
        <p>
            <a title="$Description.ATT">$Title</a>
            <span class="steps-in-sequence">Step $CurrentRecordPositionInSequence / $TotalNumberOfPages.</span>
        </p>
    <% end_with %>
        <p>
            <a href="$PreviousSequenceLink" class="previous">$PreviousPageObject.Title</a>
            <a href="$NextSequenceLink" class="next">$NextPageObject.Titl</a>
        </p>
    <% with $PreviousAndNextProvider %>
    <% if $AllPages %>
        <ul>
            <% loop $AllPages %><li><a href="$FrontEndEditLink">$Title</a></li><% end_loop %>
        </ul>
    <% end_if %>
    <% end_with %>
    <p>
        <a href="$StopSequenceLink">Quit Sequence Editing</a>
    </p>
<% else %>
    <% with $PreviousAndNextProvider %>
    <% if $ListOfSequences %>
    <div class="readOnlyLink list-of-sequences">
        <h3>Start a data-entry wizard:</h3>
        <ul>
            <% loop $ListOfSequences %><li><a href="$Link" title="$Description.ATT" class="externalLink">$Title</a></li><% end_loop %>
        </ul>
    </div>
    <% end_if %>
    <% end_with %>
<% end_if %>
</div>
