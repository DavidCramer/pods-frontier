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

## Before & After

Wrap templace code with [before][/before] & [after][/after] to mark the areas as before and after the template. This will allow you to set things like <table> or <ul> at a root level so that it is not repeated with every record.

These tags can only be used once in a template. Creating a second set will result in the code simply being removed.

When traversing, the tags are not required since you have the [each field] to control the loop and the code outside is only repeated by the parent.
```html
<ul> [each event] <li> {@post_title} </li>[/each] </ul>
```

## The [once]

Wrap templace code with [once][/once] to mark the code as only a single instance. This is useful for using keeping <script> or any code from repeating.
Unlike the [before] and [after], this can be used anywhere and have multiple instances. Code is checked to be unique:
```html

<h3>Something here</h3>
[once]<button>Send</button>[/once]
<h3>Another something</h3>
[once]<button>Send</button>[/once]

```
In this example, only a single button will be rendered as the content is not unique, however adding an ID, class or simply the button text, will correct it.
```html

<h3>Something here</h3>
[once]<button id="first">Send</button>[/once]
<h3>Another something</h3>
[once]<button id="second">Send</button>[/once]

```




