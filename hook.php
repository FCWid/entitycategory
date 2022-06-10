<?php

/**
 * Install the plugin
 *
 * @return boolean
 */
function plugin_entitycategory_install()
{
    global $DB;

    if (!$DB->tableExists(getTableForItemType('PluginEntitycategoryEntitycategory'))) {
        $create_table_query = "
            CREATE TABLE IF NOT EXISTS `" . getTableForItemType('PluginEntitycategoryEntitycategory') . "`
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `entity_id` INT(11) NOT NULL,
                `category_ids` TEXT NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`entity_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
        ";
        $DB->query($create_table_query) or die($DB->error());
    }

    return true;
}

/**
 * Uninstall the plugin
 *
 * @return boolean
 */
function plugin_entitycategory_uninstall()
{
    global $DB;

    $tables_to_drop = [
        getTableForItemType('PluginEntitycategoryEntitycategory'),
    ];

    $drop_table_query = "DROP TABLE IF EXISTS `" . implode('`, `', $tables_to_drop) . "`";

    return $DB->query($drop_table_query) or die($DB->error());
}

/**
 * Hook callback when a entity is shown
 *
 * @param Entity $entity
 */
function plugin_entitycategory_post_show_entity(Entity $entity)
{
    if ($entity->getId() > 0) {
        $categories = PluginEntitycategoryEntitycategory::getAllCategories();
        $selected_categories = PluginEntitycategoryEntitycategory::getSelectedCategoriesForEntity($entity);
        $dom = '';
        $dom .= '<div id="entitycategory_content">';
        $dom .= '<table class="tab_cadre_fixe" >' . "\n";
        $dom .= '<tbody>' . "\n";
        $dom .= '<tr class="tab_bg_1">' . "\n";
        $dom .= '<th colspan="2" class="subheader">';
        $dom .= 'Catégories refusées';
        $dom .= '</th>' . "\n";
        $dom .= '<th colspan="2" class="subheader">';
        $dom .= 'Catégories autorisées';
        $dom .= '</th>' . "\n";
        $dom .= '</tr>' . "\n";
        $dom .= '<tr class="tab_bg_1">' . "\n";
        $dom .= '<td colspan="2">' . "\n";
        $dom .= '<input type="hidden" name="entitycategory_allowed_categories" id="entitycategory_allowed_categories_ids" value="' . implode(', ', array_keys($selected_categories)) . '" />';
        $dom .= '<div>';
        $dom .= '<input type="button" class="submit" id="entitycategory_allow_categories" value="Autoriser >" style="padding: 10px" />';
        $dom .= '</div>' . "\n";
        $dom .= '<select id="entitycategory_denied_categories" style="min-width: 150px; height: 150px; margin-top: 15px;" multiple>' . "\n";

        foreach ($categories as $details) {
            if (!isset($selected_categories[$details['id']])) {
                $dom .= '<option value="' . $details['id'] . '">';
                $dom .= $details['completename'];
                $dom .= '</option>' . "\n";
            }
        }

        $dom .= '</select>' . "\n";
        $dom .= '</td>' . "\n";
        $dom .= '<td colspan="2">' . "\n";
        $dom .= '<div>';
        $dom .= '<input type="button" class="submit" id="entitycategory_deny_categories" value="< Refuser" style="padding: 10px" />';
        $dom .= '</div>' . "\n";
        $dom .= '<select id="entitycategory_allowed_categories" style="min-width: 150px; height: 150px; margin-top: 15px;" multiple>' . "\n";

        foreach ($selected_categories as $category_id => $completename) {
            $dom .= '<option value="' . $category_id . '">';
            $dom .= $completename;
            $dom .= '</option>' . "\n";
        }

        $dom .= '</select>' . "\n";
        $dom .= '</td>' . "\n";
        $dom .= '</tbody>' . "\n";
        $dom .= '</table>' . "\n";
        $dom .= '</div>' . "\n";

        echo $dom;

        $js_block = '
            var _entitycategory_content = $("#entitycategory_content");
            $(_entitycategory_content.html()).detach().insertAfter("table#mainformtable");
            _entitycategory_content.remove();

            var _entitycategory_selected_categories = {
                "denied": [],
                "allowed": []
            };

            var _entitycategory_denied_categories = $("#entitycategory_denied_categories");
            var _entitycategory_allowed_categories = $("#entitycategory_allowed_categories");

            var _entitycategory_allowed_categories_ids_elm = $("#entitycategory_allowed_categories_ids");
            var _entitycategory_allowed_categories_ids = [];

            if (_entitycategory_allowed_categories_ids_elm.val()) {
                _entitycategory_allowed_categories_ids = _entitycategory_allowed_categories_ids_elm.val().split(", ");
            }

            _entitycategory_denied_categories.on("change", function(e) {
                var selection = $(this).val();

                if (selection === null) {
                    selection = [];
                }

                _entitycategory_selected_categories.denied = selection;
            });

            _entitycategory_allowed_categories.on("change", function(e) {
                var selection = $(this).val();

                if (selection === null) {
                    selection = [];
                }

                _entitycategory_selected_categories.allowed = selection;
            });

            $("#entitycategory_allow_categories").on("click", function(e) {
                if (_entitycategory_selected_categories.denied.length) {
                    var
                        current_category_id,
                        current_category_option
                    ;

                    for (var i in _entitycategory_selected_categories.denied) {
                        current_category_id = _entitycategory_selected_categories.denied[i];
                        current_category_option = $("option[value=" + current_category_id + "]", _entitycategory_denied_categories);
                        _entitycategory_allowed_categories.append("<option value=\"" + current_category_id + "\">" + current_category_option.text() + "</option>");
                        current_category_option.remove();

                        _entitycategory_allowed_categories_ids.push(current_category_id);
                    }

                    _entitycategory_allowed_categories_ids_elm.val(_entitycategory_allowed_categories_ids.join(", "));
                }
            });

            $("#entitycategory_deny_categories").on("click", function(e) {
                if (_entitycategory_selected_categories.allowed.length) {
                    var
                        current_category_id,
                        current_category_option,
                        allowed_category_idx
                    ;

                    for (var i in _entitycategory_selected_categories.allowed) {
                        current_category_id = _entitycategory_selected_categories.allowed[i];
                        current_category_option = $("option[value=" + current_category_id + "]", _entitycategory_allowed_categories);
                        _entitycategory_denied_categories.append("<option value=\"" + current_category_id + "\">" + current_category_option.text() + "</option>");
                        current_category_option.remove();

                        allowed_category_idx = _entitycategory_allowed_categories_ids.indexOf(current_category_id);

                        if (allowed_category_idx > -1) {
                            _entitycategory_allowed_categories_ids.splice(allowed_category_idx, 1);
                        }
                    }

                    _entitycategory_allowed_categories_ids_elm.val(_entitycategory_allowed_categories_ids.join(", "));
                }
            });
        ';

        echo Html::scriptBlock($js_block);
    }
}

/**
 * Hook callback before a entity is updated
 *
 * @param Entity $entity
 */
function plugin_entitycategory_entity_update(Entity $entity)
{
    if (isset($entity->input['entitycategory_allowed_categories'])) {
        $allowed_categories_ids = trim($entity->input['entitycategory_allowed_categories']);

        $selected_categories = PluginEntitycategoryEntitycategory::getSelectedCategoriesForEntity($entity);
        $selected_categories_ids = implode(', ', array_keys($selected_categories));

        if ($allowed_categories_ids != $selected_categories_ids) {
            $entity_category = new PluginEntitycategoryEntitycategory();
            //$exists = $entity_category->getFromDBByQuery("WHERE TRUE AND entity_id = " . $entity->getId());
            $exists = $entity_category->getFromDBByCrit(["entity_id" => $entity->getId()]);
            $entity_update_params = [
                'entity_id' => $entity->getId(),
                'category_ids' => $allowed_categories_ids,
            ];

            if ($exists) {
                $entity_update_params['id'] = $entity_category->getId();
                $entity_category->update($entity_update_params, [], false);
            } else {
                $entity_category->add($entity_update_params, [], false);
            }
        }
    }
}

/**
 * Hook callback when a ticket is shown
 *
 * @param Ticket $ticket
 */
function plugin_entitycategory_post_show_ticket(Ticket $ticket)
{
    global $CFG_GLPI;
    $get_user_categories_url = PLUGIN_ENTITYCATEGORY_WEB_DIR. '/ajax/get_user_categories.php';

    $js_block = '
        //console.log("plugin_entitycategory_post_show_ticket");
        var requester_user_id = 0;
        ';
    $user_id = $_SESSION['glpiID'];
    $js_block .= 'var requester_user_id = ' . $user_id . ';';
    $js_block .= 'var glpi_csrf_token = \'' . Session::getNewCSRFToken() . '\';';
    if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
        $js_block .= '
            var requester_user_id_input = $("select[id^=dropdown__users_id_requester]");
            if (requester_user_id_input.length) {
                var requester_user_id = parseInt(requester_user_id_input.val());
            }
            ';
    }
    $selectedItilcategoriesId = '';
    if (isset($_POST['itilcategories_id'])) {
        $selectedItilcategoriesId = $_POST['itilcategories_id'];
    }

    //$js_block .= 'console.log(requester_user_id);';
    $js_block .= ' 
        if (requester_user_id) { 
            loadAllowedCategories('.$selectedItilcategoriesId.');
        }
        function loadAllowedCategories(selectedItilcategoriesId) {
                       
            $.ajax("' . $get_user_categories_url . '", {
                method: "POST",
                cache: false,
                data: {
                    requester_user_id: requester_user_id,
                    _glpi_csrf_token: glpi_csrf_token,
                    selectedItilcategoriesId : selectedItilcategoriesId
                },
                complete: function(responseObj, status) {
                    if ( status == "success"  && responseObj.responseText.length) 
                    {
                        try {
                            var allowed_categories = $.parseJSON(responseObj.responseText);
                            displayAllowedCategories(allowed_categories);
                        } catch (e) {
                        }
                    }
                }
            });
            
        };

        function displayAllowedCategories(allowed_categories) {

            var category_container = $("#show_category_by_type");
            domElementItilcategorieselement = $("select[name=itilcategories_id]");
            idSelectItil = $("select[name=itilcategories_id]").attr(\'id\');
            //idSelectItil = oldIdSelectItil;
            //surcharge id : 
            //idSelectItil = oldIdSelectItil + "_override-new";
            //domElementItilcategorieselement.attr(\'id\',idSelectItil);
            
            $("#"+idSelectItil).empty().select2({
                data: allowed_categories,
                width: "auto",
            });
    
            $("#"+idSelectItil).select2("open");
        };
        
        $( document ).ajaxComplete(function( event, xhr, settings ) {           
            if ( settings.url === "/ajax/getDropdownValue.php" ) {
                loadAllowedCategories('.$selectedItilcategoriesId.');
            }
          });
    ';

    echo Html::scriptBlock($js_block);
}
