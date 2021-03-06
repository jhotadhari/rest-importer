jQuery( document ).ready( function( $ ) {
	
		
		
	function isJSON( str ) {
		if ( /^\s*$/.test( str ) ) return false;
		str = str.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@');
		str = str.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
		str = str.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
		return (/^[\],:{}\s]*$/).test(str);
	}

	
	
	function get_tree_data( string ){
		
		var stringSample = '[{"text":"root","icon":true,"li_attr":{"id":"j1_1","class":"no_dragging"},"a_attr":{"href":"#","id":"j1_1_anchor"},"state":{"loaded":true,"opened":true,"selected":true,"disabled":true},"data":{},"children":[{"id":"j1_2","text":"sample node","icon":true,"li_attr":{"id":"j1_2"},"a_attr":{"href":"#","id":"j1_2_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"children":[]}]}]';
		
		if ( 'undefined' === typeof( string ) || string.length === 0 ){
			return $.parseJSON( stringSample );
		}
		
		if ( ! isJSON( string ) ){
			return stringSample;
		}
		
		try {
			return $.parseJSON( string );
		} catch (e) {
			return $.parseJSON( stringSample );
		}
		
	}
		
	

	
	// config tree
	var treeConf = {
		plugins: [
			'grid',
			'contextmenu',
			'wholerow',
			'dnd',	// Drag & drop
			// 'unique',
		],
		grid: {
			contextmenu: true,
			gridcontextmenu: function (grid,tree,node,val,col,t,target) {
				var contextMenu = true;
				if ( col.value === 'table' ){
					contextMenu = {
						object: {
							label: 'Object',
							action: function (data) {
								var newValue = 'object';
								var obj = t.get_node(node);
								obj.data[col.value] = newValue;
								target.innerText = newValue;
								target.textContent = newValue;
								
								// trigger changed.jstree
								t.deselect_all(true);
								t.select_node(node);
								
							}
						},
						object_meta: {
							label: 'Object-meta',
							action: function (data) {
								var newValue = 'object-meta';
								
								var obj = t.get_node(node);
								obj.data[col.value] = newValue;
								target.innerText = newValue;
								target.textContent = newValue;
								
								// trigger changed.jstree
								t.deselect_all(true);
								t.select_node(node);
							}
						},
						none: {
							label: '',
							action: function (data) {
								var newValue = '';
								
								var obj = t.get_node(node);
								obj.data[col.value] = newValue;
								target.innerText = newValue;
								target.textContent = newValue;
								
								// trigger changed.jstree
								t.deselect_all(true);
								t.select_node(node);
							}
						},
					};
				} else {
					contextMenu = {
						edit: {
							label: 'Rename',
							action: function (data) {
								var obj = t.get_node(node);
								grid._edit(obj,col,target);
							}
						}
					};
				}
				return contextMenu;
			},
			columns: [
				{
					header:'Object or Object-Meta',
					value:'table',
				},
				{
					header:'Option Key',
					value:'key',
				},
				{
					tree: true,
					header:'Option Value (Response Structure)',
				},
			],
		},
		dnd:{
		
			is_draggable: function (nodes) {
				var i = 0, j = nodes.length;
				for(; i < j; i++) {
				   if(this.get_node(nodes[i], true).hasClass('no_dragging')) {
					   return false;
				   }
				}
				return true;
			}
		},
		contextmenu:{
			items: contextmenu_item_cb
		},
		core: {
			check_callback: true,
			contextmenu: true
		} 		
	};		
	
	
	
	// init tree
	function init_tree(){
		// $('.cmb-type-tree .cmb2-tree-wrapper > .jstree').each(function(){
		$('.cmb-type-tree .cmb2-tree-wrapper ').each(function(){
			
			var $this = $( this );
			var $tree = $this.children('.jstree-grid-wrapper');

			// input element
			var $dataInput = $this.children( '.tree-data' );

			// get data from input element
			treeConf.core.data = get_tree_data( $dataInput.val() );

			// init
			var instance = $tree
				.on( 'changed.jstree rename_node.jstree update_cell.jstree-grid' , function () {
					update_input();
				})
				.jstree( treeConf );
				
			// update input element
			function update_input(){
				var instance = $tree.jstree(true);
				var data_json_string = JSON.stringify( instance.get_json() );
				$dataInput.val( data_json_string ).change();
			}
		
		});
	
	}
	
	
	// init on page load
	init_tree();

	// init on cmb2_add_row
	var cmb = window.CMB2;
	cmb.metabox().on('cmb2_add_row', function( e ) {
		init_tree();
	});
	
	
	
	
	
	
	
	
	
	
	
	

	// more or less a copy of the original, but will not apply different menu to root node
	function contextmenu_item_cb(o, cb) {
		if (o.parents.length < 2) {
			return {
				"create" : {
					"separator_before"	: false,
					"separator_after"	: true,
					"_disabled"			: false, //(this.check("create_node", data.reference, {}, "last")),
					"label"				: "Create",
					"action"			: function (data) {
						var inst = $.jstree.reference(data.reference),
							obj = inst.get_node(data.reference);
						inst.create_node(obj, {}, "last", function (new_node) {
							setTimeout(function () { inst.edit(new_node); },0);
						});
					}
				}
			};
		} else {
			return {
				"create" : {
					"separator_before"	: false,
					"separator_after"	: true,
					"_disabled"			: false, //(this.check("create_node", data.reference, {}, "last")),
					"label"				: "Create",
					"action"			: function (data) {
						var inst = $.jstree.reference(data.reference),
							obj = inst.get_node(data.reference);
						inst.create_node(obj, {}, "last", function (new_node) {
							setTimeout(function () { inst.edit(new_node); },0);
						});
					}
				},
				"rename" : {
					"separator_before"	: false,
					"separator_after"	: false,
					"_disabled"			: false, //(this.check("rename_node", data.reference, this.get_parent(data.reference), "")),
					"label"				: "Rename",
					"shortcut"			: 113,

					"shortcut_label"	: "F2",

					"action"			: function (data) {
						var inst = $.jstree.reference(data.reference),
							obj = inst.get_node(data.reference);
						inst.edit(obj);
					}
				},
				"remove" : {
					"separator_before"	: false,
					"icon"				: false,
					"separator_after"	: false,
					"_disabled"			: false, //(this.check("delete_node", data.reference, this.get_parent(data.reference), "")),
					"label"				: "Delete",
					"action"			: function (data) {
						var inst = $.jstree.reference(data.reference),
							obj = inst.get_node(data.reference);
						if(inst.is_selected(obj)) {
							inst.delete_node(inst.get_selected());
						}
						else {
							inst.delete_node(obj);
						}
					}
				},
				// "ccp" : {
				// 	"separator_before"	: true,
				// 	"icon"				: false,
				// 	"separator_after"	: false,
				// 	"label"				: "Edit",
				// 	"action"			: false,
				// 	"submenu" : {
				// 		"cut" : {
				// 			"separator_before"	: false,
				// 			"separator_after"	: false,
				// 			"label"				: "Cut",
				// 			"action"			: function (data) {
				// 				var inst = $.jstree.reference(data.reference),
				// 					obj = inst.get_node(data.reference);
				// 				if(inst.is_selected(obj)) {
				// 					inst.cut(inst.get_top_selected());
				// 				}
				// 				else {
				// 					inst.cut(obj);
				// 				}
				// 			}
				// 		},
				// 		"copy" : {
				// 			"separator_before"	: false,
				// 			"icon"				: false,
				// 			"separator_after"	: false,
				// 			"label"				: "Copy",
				// 			"action"			: function (data) {
				// 				var inst = $.jstree.reference(data.reference),
				// 					obj = inst.get_node(data.reference);
				// 				if(inst.is_selected(obj)) {
				// 					inst.copy(inst.get_top_selected());
				// 				}
				// 				else {
				// 					inst.copy(obj);
				// 				}
				// 			}
				// 		},
				// 		"paste" : {
				// 			"separator_before"	: false,
				// 			"icon"				: false,
				// 			"_disabled"			: function (data) {
				// 				return !$.jstree.reference(data.reference).can_paste();
				// 			},
				// 			"separator_after"	: false,
				// 			"label"				: "Paste",
				// 			"action"			: function (data) {
				// 				var inst = $.jstree.reference(data.reference),
				// 					obj = inst.get_node(data.reference);
				// 				inst.paste(obj);
				// 			}
				// 		}
				// 	}
				// }
			};
		}
	}

	
});