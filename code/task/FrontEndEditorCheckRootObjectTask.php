<?php


class FrontEndEditorCheckRootObjectTask extends BuildTask {

	private static $root_object_class_name = "SiteTree";

	private static $delete_unlinked_object = false;

	protected $title = "Front End Editor: check root tasks";

	protected $description = "
		Check if all the front-end editable objects are in good health. ";

	function run($request){
		$rootObjectClassName = $this->Config()->get("root_object_class_name");
		$delete = $this->Config()->get("delete_unlinked_object");
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');		
		$array = ClassInfo::subclassesFor("DataObject");
		foreach($array as $key => $className) {
			if(is_subclass_of($className, "FrontEndEditable")) {
				$objects = $className::get();
				echo "<h2>".$className."</h2>";
				foreach($objects as $obj) {
					$save = false;
					if(!$obj->FrontEndRootCanEditObject) {
						$save = true;
					}
					$array = explode(",",$obj->FrontEndRootCanEditObject);
					if(count($array) != 2) {
						$save = true;
					}
					else {
						$className = $array[0];
						$id = $array[1];
						if(!class_exists($className)) {
							$save = true;
						}
						if(!$id) {
							$save = true;
						}
						if(!$save) {
							$rootObject = $className::get()->byID($id);
							if(!$rootObject) {
								$save = true;
							}
							if(!($rootObject instanceof $rootObjectClassName)) {
								$save = true;
							}
						}
					}
					if($save) {
						if($obj->ClassName == $rootObjectClassName) {
							//do nothing...
						}
						elseif($obj instanceof SiteTree) {
							$origStage = Versioned::current_stage();
							if($delete) {
								foreach(array("Live", "Stage") as $stage) {
									Versioned::reading_stage($stage);
									$record = DataObject::get_by_id("SiteTree", $obj->ID);
									
									$descRemoved = '';
									$descendantsRemoved = 0;
									$recordTitle = $record->Title;
									$recordID = $record->ID;
									
									// before deleting the records, get the descendants of this tree
									if($record) {
										$descendantIDs = $record->getDescendantIDList();
										// then delete them from the live site too
										$descendantsRemoved = 0;
										foreach( $descendantIDs as $descID )
											if( $descendant = DataObject::get_by_id('SiteTree', $descID) ) {
												$descendant->doDeleteFromLive();
												$descendantsRemoved++;
											}
										// delete the record
										if($stage == "Live") {
											$record->doDeleteFromLive();
										}
										else {
											$record->delete();
										}
									}
									Versioned::reading_stage('Stage');
								}
								Versioned::reading_stage($origStage);
							}
							else {
								$obj->writeToStage("Stage");
								$obj->publish("Stage", "Live");
							}
						}
						else {
							if($delete) {
								$obj->delete();
							}
							else {
								$obj->write();
							}
						}
					}
					DB::alteration_message($obj->ID.": ".$obj->FrontEndRootCanEditObject, ($save ? "deleted" : "created"));
				}
			}
		}
		echo "==========================";
	}

}
