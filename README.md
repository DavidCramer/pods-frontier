Pods Frontier v1
==================
Advanced templates and layouts for Pods.


Using an intuitive visual editor, Pods Frontier allows you to create grid-based layouts, using Pods Templates or Forms as display components. In addition you can use the interface to easily create queries to control which items are shown in each element. All of these elements are outputted with a one shortcode.

Pods Frontier also adds new capabilities to Pods Templates making it easier than ever to create powerful Pods Templates, without any php code. These capabilities include conditional statements and loops for relationship fields. In addition, Pods Frontier adds style and script fields to Pods Templates allowing you to set custom CSS or JavaScript for your template.

### Frontier Layouts

#### Layouts

#### Forms

####Custom Queries


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
