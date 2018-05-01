<% if $ListID %>
  <% include Page\Form %>
<% else %>
  <% include Alert Text=$NoListMessage %>
<% end_if %>
