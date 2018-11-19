<?
namespace Thurly\Tasks\DB;

class TreeException 					extends \Thurly\Main\SystemException {};
class TreeNodeNotFoundException 		extends TreeException {};
class TreeTargetNodeNotFoundException	extends TreeNodeNotFoundException {};
class TreeParentNodeNotFoundException 	extends TreeNodeNotFoundException {};
class TreeLinkExistsException 			extends TreeException {};