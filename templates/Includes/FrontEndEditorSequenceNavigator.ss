<div class="sequence-navigator">
<% if $HasSequence %>
<<<<<<< HEAD
        <%-- <h3>current data-entry wizard</h3> --%>
=======

>>>>>>> 6c1acf3d5b1f5fbfce6b49a6530bc7074e85643b
    <% with $CurrentSequence %>
        <p class="intro-sequence">
            <h3><a title="$Description.ATT">$Title Wizard</a>
                <a href="$Top.StopSequenceLink" class="quit-sequence special-action">✖</a>
            </h3>
            <% if $TotalNumberOfPages %><span class="steps-in-sequence">Step $CurrentRecordPositionInSequence / $TotalNumberOfPages</span><% end_if %>
        </p>
        <hr/>
    <% end_with %>

<<<<<<< HEAD
    <%-- <p class="prev-next-for-sequence">
        <a href="$PreviousSequenceLink" class="previous special-action">prev</a><% end_if %>
        <a href="$NextSequenceLink" class="next special-action">next</a>
    </p> --%>
=======
>>>>>>> 6c1acf3d5b1f5fbfce6b49a6530bc7074e85643b
    <% if canGoPreviousOrNextPage %>
        <p class="prev-next-for-sequence">
            <% if canGoPreviousPage %><a href="$PreviousSequenceLink" class="previous special-action">$PreviousPageObject.Title</a><% end_if %>
            <% if canGoNextPage %><a href="$NextSequenceLink" class="next special-action">$NextPageObject.Title</a><% end_if %>
        </p>
    <% end_if %>
    <% if $AllPages %>
        <ul>
<<<<<<< HEAD
        <% loop $AllPages %><% if $exists %><li class="$SequenceLinkingMode doable"><a href="$FrontEndEditLink">$SequenceTitle</a><% else %><li class="$SequenceLinkingMode">$Title None is here<% end_if %></li><% end_loop %>
        </ul>
    <% end_if %>
    <%-- <hr/>
    <p class="quit-sequence">
        <a href="$StopSequenceLink" class="special-action">✖</a>
    </p> --%>
=======
        <% loop $AllPages %><% if $exists %><li class="$SequenceLinkingMode doable"><a href="$FrontEndEditLink">$SequenceTitle</a><% else %><li class="$SequenceLinkingMode">$SequenceTitle<% end_if %></li><% end_loop %>
        </ul>
    <% end_if %>
>>>>>>> 6c1acf3d5b1f5fbfce6b49a6530bc7074e85643b
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
