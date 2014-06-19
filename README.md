Pods Frontier
==========
Advanced templates and simple form and layouts for Pods.


Using an intuitive visual editor, Pods Frontier allows you to create grid-based layouts, using Pods Templates or Forms as display components. In addition you can use the interface to easily create queries to control which items are shown in each element. All of these elements are outputted with a one shortcode.

Pods Frontier also adds new capabilities to Pods Templates making it easier than ever to create powerful Pods Templates, without any php code. These capabilities include conditional statements and loops for relationship fields. In addition, Pods Frontier adds style and script fields to Pods Templates allowing you to set custom CSS or JavaScript for your template.

### Frontier Layouts and Forms

To create a new layout go to the Frontier from the Pods Menu and select "Add New". In the pop-up enter a name for your new Frontier. You will then need to choose if your Frontier is a form or a layout. You will also need to add a description and click create.

Your Frontier can be a layout or a form. The form editor will allow you to create responsive forms, while the layout editor will allow you to combine Frontier Forms, Pods Templates and custom queries to easily create complex, responsive layouts. Both editors use a simple, yet powerful drag and drop interface.

#### Forms
If you choose to create a form, the first step is to load a Pod from the drop-down menu on the right side of the "Layout Builder" tab. Once you have done that, you will see all fields of the selected Pod are now available on the right side of the editor. Simply drag and drop the fields into layout containers to build your form.

You can add or remove layout containers, or rows of containers both vertically and horizontally by hovering near the borders of any layout container and clicking the plus or minus icons. Additionally you can rearrange rows by dragging and dropping them.

In the "Grid" tab you can customize the classes given to the classes that will wrap your form as well as the rows and containers that make it up. This will allow you to further customize your forms from a external CSS stylesheet or script.

From the "Form Settings" tab you can set the URL the form redirects to using the "Thank you URL" field and change the text for the submit button using the "Button Label".

#### Layouts
In the "Layout Builder" tab, you will see all Pods Templates and Frontier forms in the far-right side of the screen, in addition to a query container option. To add a template or form to a container, simply drag and drop it form the list on the right side into a container in the layout.

You can add or remove layout containers, or rows of containers both vertically and horizontally by hovering near the borders of any layout container and clicking the plus or minus icons. Additionally you can rearrange rows by dragging and dropping them.

By default Pods Templates add to the layout will be populated with WHAT ITEMS? To modify which items are used to populate the template, you need to add a query container. To do this, drag a query into a layout container before the template(s) you want to affect and then click the query container.

In the query container, you will first need to select a Pod to filter based on. Then you can create one or more conditions to limit your items. Setting your filters is easy as all options, and comparisons are set from drop-downs.

#### Outputting Your Frontier
To output your Frontier layout simply copy the shortcode from the main Frontier screen and place it in a WordPress post or page. Alternatively, you can call it in a theme ro plugin file using `do_shortcode()`.

![Pods Admin -> Add New](https://github.com/pods-framework/pods-gravity-forms/blob/master/screenshot-1.png?raw=true)


### Templates

#### Conditionals
Pods Frontier adds the ability to use [if] tags in your templates to conditionally show fields or markup. For example:

```html
[if book_title="The Hobbit"]{@book_title} or There and Back Again[/if]
```

@TODO Add screenshot

#### Loops
Pods Frontier enables easy looping via the [each] tag. This first example shows how to loop through all entries in a relationship field (in this case a media field) aadding markup to each entry:

```html
[each cover_images]
    <img class="cover-images-in-template" src="{@cover_images._src}" /></div>
[/each]
```

When using the [each] loop you can also specify code to run before, after and only once, like in this example:

```html
[each cover_images]
    [before]
        <!-- Carousel items --><div class="carousel-inner">
        <h3>Book</h3>
    [/before]
        <div class="[once]active [/once]item"><img src="{@cover_images._src}" /></div>
    [after]</div><!--.carousel-inner-->[/after]
[/each]
```

This code will output the code in the [before] and [after] blocks will run before and after the looping items, but only if there are items to loop. The code in the [once] block will only run on the first iteration.

@TODO Add screenshot
