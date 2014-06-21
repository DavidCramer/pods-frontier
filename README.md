<p align="center">
  <img src="./frontier-rectangle-logo.png" alt="Pods Frontier" width="80%" height="80%" />
</p>

<h1 align="center">Pods Frontier</h1>
<p align="center">
Advanced templates and simple form and layouts for Pods.
</p>
<p>
<br />
<em>Please Note: This plugin is currently in beta development. Not all functionality described here is completed. <strong>Please test thoroughly before using in production</strong>.</em>.
</p>

If you find a bug or have an idea to improve this plugin, [please open an issue](https://github.com/pods-framework/pods-frontier/issues/)

Using an intuitive visual editor, Pods Frontier allows you to create grid-based layouts, using Pods Templates or Forms as display components. In addition you can use the interface to easily create queries to control which items are shown in each element. All of these elements are outputted with a one shortcode.

Pods Frontier also adds new capabilities to Pods Templates making it easier than ever to create powerful Pods Templates, without any php code. These capabilities include conditional statements and loops for relationship fields. In addition, Pods Frontier adds style and script fields to Pods Templates allowing you to set custom CSS or JavaScript for your template.

Requires [Pods](http://pods.io) version 2.4.1 or later.

### Frontier Layouts and Forms

To create a new Frontier layout or form go to the Frontier from the Pods Menu and select "Add New". In the pop-up enter a name for your new Frontier. You will then need to choose if your Frontier is a form or a layout. You will also need to add a description and click create.

Your Frontier can be a layout or a form. The form editor will allow you to create responsive forms, while the layout editor will allow you to combine Frontier Forms, Pods Templates and custom queries to easily create complex, responsive layouts. Both editors use a simple, yet powerful drag and drop interface.

#### Forms
If you choose to create a form, the first step is to load a Pod from the drop-down menu on the right side of the "Layout Builder" tab. Once you have done that, you will see all fields of the selected Pod are now available on the right side of the editor. Simply drag and drop the fields into layout containers to build your form.

![Form Layout Builder](/screenshots/screenshot-4.png)

You can add or remove layout containers, or rows of containers both vertically and horizontally by hovering near the borders of any layout container and clicking the plus or minus icons. Additionally you can rearrange rows by dragging and dropping them.

In the "Grid" tab you can customize the classes given to the classes that will wrap your form as well as the rows and containers that make it up. This will allow you to further customize your forms from a external CSS stylesheet or script.

From the "Form Settings" tab you can set the URL the form redirects to using the "Thank you URL" field and change the text for the submit button using the "Button Label".

#### Layouts
In the "Layout Builder" tab, you will see all Pods Templates and Frontier forms in the far-right side of the screen, in addition to a query container option. To add a template or form to a container, simply drag and drop it form the list on the right side into a container in the layout.

![Layout Builder](/screenshots/screenshot-1.png)

You can add or remove layout containers, or rows of containers both vertically and horizontally by hovering near the borders of any layout container and clicking the plus or minus icons. Additionally you can rearrange rows by dragging and dropping them.

<em><strong>NOTE:</strong> Query containers do not currently function properly. See [issue #23][https://github.com/pods-framework/pods-frontier/issues/23]</em>

By default Pods Templates add to the layout will be populated with all items of the Pod set as the base Pod for this Frontier. To modify which items are used to populate the template, you need to add a query container. To do this, drag a query into a layout container before the template(s) you want to affect and then click the query container.

In the query container, you will first need to select a Pod to filter based on. Then you can create one or more conditions to limit your items. Setting your filters is easy as all options, and comparisons are set from drop-downs.

![Query Builder](/screenshots/screenshot-3.png)

#### Outputting Your Frontier
To output your Frontier layout or form, simply copy the shortcode from the main Frontier screen and place it in a WordPress post or page. Alternatively, you can call it in a theme or plugin file using `do_shortcode()`.

![Output Frontier](/screenshots/screenshot-5.png)

### Templates

#### Adding JavaScript and CSS Stylesheets
Frontier adds two new tabs to the template editor: "Scripts" and "Styles". The scripts tab allows you to enter JavaScript and the "Styles" tab allows you to add CSS styles. The contents of these tabs will be outputted, wrapped in the appropriate style script tags whenever the template is loaded using a Frontier layout or the plugin Pods Frontier Auto Template. The scripts will be outputted in the site footer and the styles in the site header. They will only be outputted once, no matter how many times the template is used on the page.

The contents of these tabs are stored as meta fields, which means they can be retrieved via `get_post_meta()`.


#### Advanced Markup Simplified
Pods Frontier allows you to use Pods Templates to create advanced markup, without using any PHP or sacrificing the ability to create complex templates. These new tools allow you to conditionally show output and create loops.

##### Loops
Pods Frontier enables easy looping via the `[each]` tag. This example shows how to loop through all entries in a relationship field--in this case a multi-select relationship field called chapters ina custom post type book--adding markup to each entry:

```html
<h3>Chapters</h3>
<ul>
[each chapters]
    <li>{@chapters.post_title}</li>
[/each]
</ul>
```

This code would output one list item(`<li>`) with the post_title for each related chapter post.

##### Conditionals
The previous example illustrating an each for a relationship field, has a major problem: if there are no chapters set it would output:

```html
<h3>Chapters</h3>
<ul>
</ul>
```

To avoid, this the each loop can be placed inside of if conditionals, so that nothing is outputted if there are no chapters to output:

```html
[if chapters]
    <h3>Chapters</h3>
    <ul>
    [each chapters]
        <li>{@chapters.post_title}</li>
    [/each]
    </ul>
[/if]
```

Now there will be no output if there are no related chapters to return. You can even take this a step further by adding alternate output, if the conditional is not met, using an else statement. For example:

```html
[if chapters]
    <h3>Chapters</h3>
    <ul>
    [each chapters]
        <li>{@chapters.post_title}</li>
    [/each]
    </ul>
[else]
    <p>Sorry, No chapters</p>
[/if]
```


If conditionals are also useful when you wish to show a label before a field, but do not want that label to appear if the field has no value. In the following example, the text "Co Author:" only appears if there is a value in the 'co_author' field.

```html
    [if co_author]
       Co Author: {@co_author}
    [/if]
```


##### Once
In many cases when doing an each loop, you may need to add markup, on the first item only. For example to apply a special CSS style or to add an "active" class for jQuery sliders, accordions or tabs. Adding to our chapters example, this next example adds a "first-chapter" class to the first chapter only:
```html
[if chapters]
    <h3>Chapters</h3>
    <ul>
    [each chapters]
        <li [once]class="first-chapter"[/once]>{@chapters.post_title}</li>
    [/each]
    </ul>
[else]
    <p>Sorry, No chapters</p>
[/if]
```

#### Before and After
Pods Frontier also ads `[before]` and `[after]` blocks. These can be used to set code that runs before or after all iterations of a template. This means that if you call a template in a Frontier layout or using the Pods shortcode, so that it is used to show multiple items, the ``[before]` and `[after]` blocks will only run once.

This avoids the issue where the shortcode `[pods name="book" template="Books"]` if it called a tempalte like this:

```html
tml
<ul>
	<li>{@post_title}</li>
</ul>
```

Would output:
```html
<ul>
	<li>Book One</li>
</ul>
<ul>
	<li>Book Two</li>
</ul>
<ul>
	<li>Book Three</li>
</ul>
```

That template could be rewritten as:
```html
[before]<ul>[/before]
	<li>{@post_title}</li>
[after]</ul>[/after]
```

Which will result in:

```html
<ul>
	<li>Book One</li>
	<li>Book Two</li>
	<li>Book Three</li>
</ul>
```


##### Putting It All Together
This template is for a custom post type called "author" with a field called "books" that is a related to the custom post type "book". It uses a conditional test to check if the author has any books, and if it does not it outputs the message "Author has no books uploaded.". If the author does have books it outputs information from each of the related books, starting with the post title. Then it loops through the field "cover_images", which is in the "book" cpt. Inside of the loop, the `[once]` block is used to add an additional class to the first item only. If this template itself is looped the wrapping container `.book-wrap` will contain all items and not be repeated.

```html
[before]<div class="book-wrap">[/before]
    [if books]
        <h5>Books</h5>
        <ul>
            [each books]
            <li>{@post_title}
            [if cover_images]
                <ul>
                    [each cover_images]
                    <li class="cover-image[once] first-image[/once]">{@_img.thumbnail}</li>
                    [/each]
                </ul>
            [else]
                <p class="no-images">No Images for this book</p>
            [/if]
            </li>
            [/each]
        </ul>
    [else]
        <p>Author has no books uploaded.</p>
    [/if]
[after]<!--.book-wrap-->[/after]
```

![Frontier Template](/screenshots/screenshot-2.png)
>>>>>>> readme
