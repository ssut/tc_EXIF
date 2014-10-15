(function($) {
    var tooltip = function() {
        /* CONFIG */
            xOffset = 5;
            yOffset = 5;
            // these 2 variable determine popup's distance from the cursor
            // you might want to adjust to get the right result
        /* END CONFIG */
        $("a.tooltip").hover(function(e){
            this.t = this.title;
            this.title = "";
            $("body").append("<p id='tooltip'>"+ this.t +"</p>");
            $("#tooltip")
                .css('position', 'absolute')
                .css('background', '#fefefe')
                .css('border', '1px solid #ddd')
                .css('padding', '5px')
                .css("top",(e.pageY - xOffset) + "px")
                .css("left",(e.pageX + yOffset) + "px")
                .fadeIn("fast");
        },
        function(){
            this.title = this.t;
            $("#tooltip").remove();
        }); 
        $("a.tooltip").mousemove(function(e){
            $("#tooltip")
                .css("top",(e.pageY - xOffset) + "px")
                .css("left",(e.pageX + yOffset) + "px");
        });
    };

    /*
     * Image preview script
     * powered by jQuery (http://www.jquery.com)
     *
     * written by Alen Grakalic (http://cssglobe.com)
     *
     * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
     *
     */

    var imagePreview = function() {
        /* CONFIG */
            xOffset = 5;
            yOffset = 5;
            // these 2 variable determine popup's distance from the cursor
            // you might want to adjust to get the right result
        /* END CONFIG */
        $("a.preview").hover(function(e){
            this.t = this.title;
            this.title = "";
            var c = (this.t != "") ? "<br/>" + this.t : "";
            $("body").append("<p id='preview'><img src='"+ this.href +"' alt='' style='max-width: 250px' />"+ c +"</p>");
            $("#preview")
                .css('position', 'absolute')
                .css("top",(e.pageY - xOffset) + "px")
                .css("left",(e.pageX + yOffset) + "px")
                .fadeIn("fast");
        },
        function(){
            this.title = this.t;
            $("#preview").remove();
        });
        $("a.preview").mousemove(function(e){
            $("#preview")
                .css("top",(e.pageY - xOffset) + "px")
                .css("left",(e.pageX + yOffset) + "px");
        });
    };

    var setButtonEvent = function() {
        $('input[name="toggleEnabled"]').click(function() {
            var self = $(this);
            var data = self.data('data');
            $.ajax({
                type: 'POST',
                url: '/plugin/EXIF/toggle',
                data: data,
                cache: false,
                beforeSend: function() {
                    self.attr('disabled', 'disabled');
                },
                success: function(data) {
                    self.removeAttr('disabled');
                    if(data.success) {
                        self.val(data.message);
                    } else alert(data.message);
                }
            });
        });

        $('input[name="deleteExif"]').click(function() {
            var self = $(this);
            var data = self.data('data');
            $.ajax({
                type: 'POST',
                url: '/plugin/EXIF/delete',
                data: data,
                cache: false,
                beforeSend: function() {
                    self.attr('disabled', 'disabled');
                },
                success: function(data) {
                    self.removeAttr('disabled');
                    if(data.success) {
                        self.parent().parent().remove();
                    }
                }
            });
        });
    };

    // starting the script on page load
    $(document).ready(function() {
        tooltip();
        imagePreview();
        setButtonEvent();
    });
})(jQuery);
