Pods Frontier Template Editor
==================

Advanced Templating, with assets and autocompletion magic loops and tags

This is a breakout of the template editor. When activated, it will modify the current Pods Templates component with the Frontier editor, tag completion, looping and field referencing.

## contextual looping

This allows you to loop through relation fields without needing to create another template. example:

```html
<div>
	<h1>{@post_title}</h1>
	<h3>Events</h3>
	<ol>
		[each event]
		<li>
			{@event.post_title}
			<h4>Reviews</h4>
			<ul>
				[each event.review]
				<li>{@event.review.post_title}</li>
				[/each]
			</ul>
			<br>
		</li>
		[/each]
	</ol>
	<hr>

</div>
````

The template code between [each field] [/each] are in context. This means that the code will be repeated using the data set for each relating record of the field.

The traversal field, in this example [each event.review] is not required. it would work fine as [each review] but keeping the dot traversal makes for easy reading.

# screenshot showing code color & tag completion
![Frontier Template](http://cl.ly/image/0Y1Y3l1v0H2S/Screen%20Shot%202014-01-18%20at%208.38.50%20PM.png)


