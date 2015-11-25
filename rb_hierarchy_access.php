<?php
// Created By Josh Willcock Copyright 2014
// Created For CLC Hierarchies System Intergration
// Category Class (used in array to hold a category with array of tag ID's inside)
class Category{
	public $id;
	public $tagarray = array();
}
// This is the class you create to run the build query function
class queryBuilder
{
	// This function requires the manager's userid to create a restrictive where clause
	public function build($userIds)
	{
		global $DB;
  	//Category Class Name of Cat (ID) & tagarray (arrayOfTagIDs)

		//ArrayToHoldCategories
		$categoryArray= array();
		//Get Manager Tags
		$managerTagResult = $DB->get_records_sql('SELECT id, tagid FROM {hierarchy_managertags} WHERE  userid = '.$userIds);
		$managerTags = array();
		foreach ($managerTagResult as $arow) {
			array_push($managerTags, $arow->tagid);
		}
		//SQL Query
		if (empty($managerTags)) {
            $results = "";
        } else {
          $ids = implode(",",$managerTags);
          $sql = 'SELECT tu.`id` as `id`, tt.`category` as `category` FROM {hierarchy_usertags} tu JOIN {hierarchy_tags} tt on (tt.`id` = tu.`tagid`) WHERE tt.`id` IN ('.$ids.')';
          $results = $DB->get_records_sql($sql);
      }
		//Loop Through Available Requested Tags
      if (!empty($results)) {
          foreach ($results as $arow) {
				//Does The Category For The Tag Already Exist
             $categoryExist=0;
             foreach($categoryArray as $category) {
                if ($category->id= = $arow->category) {
    					//If Category Already Exists Add Tag Data To Category
                   $categoryExist=1;
                   array_push($category->tagarray, $arow->id);
               }
           }
                //Check if category exists
           if ($categoryExist == 0) {
                    //If Category Does Not Exist Create Category Object, Then Add Tag Data To Category
            $newCategory = new Category();
            $newCategory->id = $arow->category;
            array_push($newCategory->tagarray, $arow->id);
            $categoryArray[] = $newCategory;
        }
    }
    		//End Of SQL Loop
    
    		//    END             OF              CODE       //
    
    		//Create SQL Query
    $queryBuilder='where ';
    $and=0;
    foreach($categoryArray as $category) {
     if ($and == 0) {$and=1;} else { $queryBuilder = $queryBuilder.' and '; }
     $queryBuilder = $queryBuilder.'(';
        $andB=0;
        foreach($category->tagarray as $tag) {
           if ($andB == 0) {$andB=1;} else { $queryBuilder = $queryBuilder.' or '; }
           $queryBuilder = $queryBuilder.' tu.`tagid`="'.$tag.'"';
       }
       $queryBuilder = $queryBuilder.') ';
}
} else {
    $queryBuilder = "";
}
return $queryBuilder;
}
}

?>