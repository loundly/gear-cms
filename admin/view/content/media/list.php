<?php
    admin::addButton('
        <a @click="addDirModal = true" class="button border">
            '.lang::get('add_dir').'
        </a>
    ');
    admin::addButton('
        <form id="upload" method="post" action="'.url::admin('content', ['media', 'upload']).'" enctype="multipart/form-data">
            <a class="button">
                '.lang::get('upload').'
            </a>
            <input class="file" type="file" name="file">
            <input type="hidden" name="path" :value="path">
        </form>
    ');
    admin::$search = true;

    $form = new form();
    $form->setHorizontal(false);

    $form->addFormAttribute('v-on:submit.prevent', 'addDir');

    $field = $form->addTextField('name', '');
    $field->fieldName(lang::get('name'));
    $field->addAttribute('v-model', 'dirName');
    $field->fieldValidate();

    $field = $form->addRawField('<p class="static">{{ path }}<span class="text-light" v-if="dirName" v-text="dirName"></span></p>');
    $field->fieldName(lang::get('path'));

?>

<modal v-if="addDirModal" @close="addDirModal = false">
    <h3 slot="header"><?=lang::get('add_dir'); ?></h3>
    <div slot="content">
        <?=$form->show(); ?>
    </div>
</modal>

<file-table :headline="headline" :search="search"></file-table>

<?php
theme::addJSCode('
    new Vue({
        el: "#app",
        data: {
            headline: lang["media"],
            path: "/",
            search: "",
            showSearch: true,
            addDirModal: false,
            dirName: ""
        },
        events: {
            path: function(data) {
                this.path = data;
                $("#upload ul").html("");
            }
        },
        created: function() {

            var vue = this;

            eventHub.$on("setPath", function(path) {
                vue.path = path;
            });

            eventHub.$on("setHeadline", function(data) {
                vue.headline = data.headline;
                vue.showSearch = data.showSearch;
            });

            $(document).ready(function() {

                $("#upload").on("click", ".button", function(e) {
                    e.preventDefault();
                    $(this).next(".file").click();
                });

                $("#upload .file").on("change", function() {
                    $("#upload").ajaxSubmit({
                    	uploadProgress: function(event, position, total, percentComplete) {
                    		var pVel = percentComplete + "%";
                    		$("#upload .button").text(lang["loading"] + " (" + pVel + ")");
                    	},
                    	complete: function(data) {
                            $("#upload .button").text(lang["upload"]);
                            jQuery.event.trigger("fetch");
                    	}
                    });
                });

            });

        },
        methods: {
            addDir: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['media', 'addDir']).'",
                    dataType: "text",
                    data: {
                        name: vue.dirName,
                        path: vue.path
                    },
                    success: function(data) {
                        jQuery.event.trigger("fetch");
                        vue.addDirModal = false;
                        vue.dirName = "";
                    }
                });

            }
        }
    });
');
?>
