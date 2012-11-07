<?php
/*
    Author: Mark Maunder <mmaunder@gmail.com>
    Author website: http://markmaunder.com/
    License: GPL 3.0
*/
include dirname(__FILE__) . '/widgets.php';
?>
<div id="sdAjaxLoading" style="display: none; position: fixed; right: 1px; top: 1px; width: 100px; background-color: #F00; color: #FFF; font-size: 12px; font-family: Verdana, arial; font-weight: normal; text-align: center; z-index: 100; border: 1px solid #CCC;">Loading...</div>
<div class="wrap">
    <h2 class="depmintHead">Manage Projects</h2>
    <table class="form-table deploymintTable">
    <tr>
        <td>Enter the name of a project to create:</td>
        <td><input type="text" id="sdProjectName" value="" size="55" maxlength="100" /></td>
    </tr>
    <tr>
        <td>Git Origin Location:</td>
        <td><input type="text" id="sdProjectOrigin" value="" size="55" maxlength="255" /></td>
    </tr>
    <tr>
        <td colspan=2><input type="button" name="but2" value="Create project" onclick="deploymint.createProject(jQuery('#sdProjectName').val(),jQuery('#sdProjectOrigin').val()); return false;" class="button-primary" /></td>
    </tr>
    </table>
    </p>
    <p id="sdProjects">
    </p>

        
</div>
<script type="text/x-jquery-tmpl" id="sdProjTmpl">
<div id="sdProj${id}">
{{each(i,proj) projects}}
<h2>Project: ${proj.name}&nbsp;<a href="#" onclick="deploymint.deleteProject(${proj.id}); return false;" style="font-size: 10px;">remove</a></h2>
<div class="depProjWrap">
    {{if proj.origin}}
    <div>
        Origin: 
        <span class="deploymint-origin" title="${proj.originAvailableMessage}">
        {{if proj.originAvailable}}
            <span class="deploymint-success">${proj.origin}</span>
        {{else}}
            <span class="deploymint-error">${proj.origin} -- Unable to connect</span>
        {{/if}}
        </span>
        <input type="button" name="edit-origin" value="Edit Origin" class="button-secondary" onclick="deploymint.editOrigin(${proj.id}, '${proj.origin}')" />
    </div>
    {{/if}}
    {{if proj.project_uuid}}
    <div>UUID: ${proj.project_uuid}</div>
    {{/if}}
    <br />
    Add a blog to this project:&nbsp;<select id="projAddSel${proj.id}">
    {{if proj.numNonmembers}}
    {{each(k,blog) proj.nonmemberBlogs}}
    <option value="${blog.blog_id}">${blog.domain}${blog.path}</option>
    {{/each}}
    {{else}}
    <option value="">--No blogs left to add--</option>
    {{/if}}
    </select>&nbsp;<input type="button" name="but12" value="Add this blog to the project" onclick="deploymint.addBlogToProject({projectID:${proj.id}, blogID:jQuery('#projAddSel${proj.id}').val()}); return false;" />
    <h3 class="depSmallHead">Blogs that are part of this project:</h3>
    {{if proj.memberBlogs.length}}
    <ul class="depList">
        {{each(l,blog) proj.memberBlogs}}
        <li>${blog.domain}${blog.path}&nbsp;<a href="#" onclick="deploymint.removeBlogFromProject({projectID:${proj.id}, blogID:${blog.blog_id}}); return false;" style="font-size: 10px;">remove</a></li>
        {{/each}}
    </ul>
    {{else}}
    <i>&nbsp;&nbsp;You have not added any blogs to this project yet.</i>
    {{/if}}
</div>
{{/each}}

</div>
</script>
<script type="text/javascript">
jQuery(function(){
    deploymint.reloadProjects();
    });
</script>