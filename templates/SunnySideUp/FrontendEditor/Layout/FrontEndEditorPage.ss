<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
    <article>
        <h1>$Title</h1>
        <div class="content">$Content</div>
    </article>
    <aside class="sidebar">
        <div id="PositionAndHistoryAside">
            <div id="PositionAside">
                <% if FrontEndEditorBreadCrumbs %>
                <h3>Position</h3>
                    <ul id="FrontEndEditorBreadCrumbs">
                    <% loop FrontEndEditorBreadCrumbs %>
                    <li class="position$Pos">
                        <% if Last %>
                            <span style="background-color: $FrontEndEditColour;">$Title</span>
                        <% else %>
                            <a href="$FrontEndEditLink" style="background-color: $FrontEndEditColour;">$FrontEndEditIcon $Title</a>
                        <% end_if %>
                        </li>
                    <% end_loop %>
                    </ul>
                <% end_if %>
            </div>
            <div id="HistoryAside">
            <% if GoBackLinks %>
            <h3>Recently Edited</h3>
            <ul id="RecentlyEdited">
            <% loop GoBackLinks %>
                <li><a href="$FrontEndEditLink">$FrontEndEditIcon <% if Title %>$Title<% else %>New $singular_name record<% end_if %></a></li>
            <% end_loop %>
            </ul>
            <% end_if %>
            </div>
        </div>
    </aside>

    <div id="FontEndEditorFormOuter"><% include FrontEndEditorPageAjaxVersion %></div>

</div>
