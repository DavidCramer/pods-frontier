pods-display-suite
==================

Frontend building system for creating templates forms and layout based around pods data structures.


This is a little explanation of my development process. I tend to work backwards. I develop the end result then build the infrastructure and management consoles to achieve the result. I'll then slowly build structures and replace the static result code with the dynamic. I feel this give me a much greater understanding on the controls required in the admin panels.
Anyone working on this will notice mention of Caldera. I have a plugin called My Shortcodes. It's a free plugin on .org which enables you to build your own shortcodes and widgets. This is the bases of the Advanced Templates component. I wanted to bring that building ability and use Pods data structures to form blocks of formatted content. My Shortcodes has a pro version called Caldera Engine and its from this version that I'm pulling some builder interface inspiration.
I use Caldera Engine to build the required result then code the management and admin panels to take over the rendering dynamically. 
So in essence caldera engine is my prototyping tool and because of this, I use some of the management options found in caldera to take over the rendering of the elements and this is where we see the crossover code.