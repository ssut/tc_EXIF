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
        var buttons = $('input[name^="batch"], table.data-inbox input[type="button"]');

        $('input[name="toggleEnabled"]').click(function() {
            var self = $(this);
            var data = self.data('data');
            $.ajax({
                type: 'POST',
                url: baseURL + '/plugin/EXIF/toggle',
                data: data,
                cache: false,
                beforeSend: function() {
                    buttons.attr('disabled', 'disabled');
                },
                success: function(data) {
                    buttons.removeAttr('disabled');
                    if(data.success) {
                        self.val(data.message);
                    } else alert(data.message);
                }
            });
        });

        $('input[name="batchToggleOn"]').click(function() {
            var self = $(this);
            var entry = self.data('entry');
            $.ajax({
                type: 'POST',
                url: baseURL + '/plugin/EXIF/toggle',
                data: {
                    type: 'batch',
                    entry_id: entry,
                    toggle: 1
                },
                cache: false,
                beforeSend: function() {
                    buttons.attr('disabled', 'disabled');
                },
                success: function(data) {
                    buttons.removeAttr('disabled');
                    if(data.success) {
                        location.reload();
                    } else alert(data.message);
                }
            });
        });

        $('input[name="batchToggleOff"]').click(function() {
            var self = $(this);
            var entry = self.data('entry');
            $.ajax({
                type: 'POST',
                url: baseURL + '/plugin/EXIF/toggle',
                data: {
                    type: 'batch',
                    entry_id: entry,
                    toggle: 0
                },
                cache: false,
                beforeSend: function() {
                    buttons.attr('disabled', 'disabled');
                },
                success: function(data) {
                    buttons.removeAttr('disabled');
                    if(data.success) {
                        location.reload();
                    } else alert(data.message);
                }
            });
        });

        $('input[name="batchDelete"]').click(function() {
            if(!confirm('Are you sure want to delete all?')) return;
            var self = $(this);
            var entry = self.data('entry');
            $.ajax({
                type: 'POST',
                url: baseURL + '/plugin/EXIF/delete',
                data: {
                    type: 'batch',
                    entry_id: entry
                },
                cache: false,
                beforeSend: function() {
                    buttons.attr('disabled', 'disabled');
                },
                success: function(data) {
                    buttons.removeAttr('disabled');
                    if(data.success) {
                        location.reload();
                    } else alert(data.message);
                }
            });
        });

        $('input[name="deleteExif"]').click(function() {
            var self = $(this);
            var data = self.data('data');
            $.ajax({
                type: 'POST',
                url: baseURL + '/plugin/EXIF/delete',
                data: data,
                cache: false,
                beforeSend: function() {
                    buttons.attr('disabled', 'disabled');
                },
                success: function(data) {
                    buttons.removeAttr('disabled');
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
