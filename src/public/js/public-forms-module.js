(function(window, $, undefined){
    var SolariaForms = {
        init: function(){
            this.forms = $('.solaria-form');
            if(this.forms.length){
                this.bindEvents();
                this.initPlugins();
            }
            return this;
        },
        bindEvents: function(){
            var self = this;
            this.forms.on('submit', function(e){
                if(self.forms.hasClass('submitted')){
                    e.preventDefault();
                    return false;
                }
                self.forms.addClass('submitted');
                var button = self.forms.find('button[type="submit"], button');
                if(button.length){
                    button.prop('disabled', true);
                }
            });
        },
        initPlugins: function(){
            var self = this;
            this.forms.find('input[type="file"]').fileupload({
                dataType: 'json',
                change: function(e, data){
                    self.showFileLoader(data.fileInputClone);
                    self.hideFileErrors(data.fileInputClone);
                },
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        if(file.error.length){
                            self.showFileErrors(data.fileInputClone, file);
                        } else {
                            self.showFileSuccess(data.fileInputClone, file);
                        }
                    });
                    self.hideFileLoader(data.fileInputClone);
                }
            });
            this.forms.find('input[type="file"]').each(function(index, file){
                var $file = $(file),
                    $hidden = $('#hidden-' + $file.attr('id'));
                if($hidden.val() != ''){
                    self.showFileSuccess($file, {name: $hidden.val()})
                }
            });
        },
        showFileLoader: function(fileInput){
            var fileContainer = $(fileInput).parents('.form-group');
            fileContainer.find('.upload-loader').removeClass('hidden');
        },
        hideFileLoader: function(fileInput){
            var fileContainer = $(fileInput).parents('.form-group');
            fileContainer.find('.upload-loader').addClass('hidden');
        },
        hideFileErrors: function(fileInput){
            var fileContainer = $(fileInput).parents('.form-group');
            fileContainer.removeClass('has-feedback has-error has-success');
            fileContainer.find('.help-block').remove();
        },
        showFileErrors: function(fileInput, file){
            var fileContainer = $(fileInput).parents('.form-group'),
                errorMessages = '<p>' + file.error.join('</p><p>') + '</p>';
            fileContainer.addClass('has-feedback has-error');
            if(!fileContainer.find('.help-block').length)
                fileContainer.append('<div class="help-block with-errors"></div>');
            fileContainer.find('.help-block').html(errorMessages);
        },
        showFileSuccess: function(fileInput, file){
            var fileContainer = $(fileInput).parents('.form-group'),
                successMessage = '<p>' + file.name + '</p>';
            fileContainer.find('#hidden-field-' + file.input).val(file.name);
            fileContainer.addClass('has-success has-feedback');
            if(!fileContainer.find('.help-block').length)
                fileContainer.append('<div class="help-block"></div>');
            fileContainer.find('.help-block').html(successMessage);
        }
    };
    $(function(){
        window.solaria_forms = SolariaForms.init();
    })
})(window, jQuery);