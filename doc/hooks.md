# Documentation of hooks in the threepeas extension

## hook_civicrm_threepeas_projectactions

This hook is executed when building a projectlist and offers the functionality to add links to additional screens. E.g. a link to linked documents

**Spec**

function hook_civicrm_threepeas_projectactions($project);

**Parameters**

* `$project` array containing the info of the project

**Return value**

Returns an array with links

**Example**

    function mymodule_civicrm_threepeas_projectactions($project) {
        $link = '<a href="civicrm/my_project_links/?pid='.$project['id'].'" title="My project info" class="action-item">My project info"</a>';
        return array($link);
    }