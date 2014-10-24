function atf_show_popup(curl) {
    var winWidth = screen.width;
    var winHeight = screen.height;
    var left = Math.round((winWidth/2) - (550/2));
    var top = 0;
    if (winHeight > 450)
        top = Math.round((winHeight/2) - (450/2));
    window.open(curl, 'ptm', 'height=450,width=550,left=' + left + ',top=' + top).focus();
    return false;
}