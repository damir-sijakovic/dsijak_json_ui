DSIJAK JSON UI
====================

Creates JSON list of menu, categories and posts.

What can this do?
-----------------

* You can create sliders, product menus without mixing with PHP code.
* You can fetch this JSON file asynchronously. This way page will load faster. 


How do I use this?
------------------

Activate plugin and click on 'Create JSON file' button.    
Then you can fetch JSON from root route:

			fetch('http://localhost:3000/dsijak-json-ui.json')
			.then(response => response.json())
			.then(data => console.log(data));
			
			

Have fun!
---------
