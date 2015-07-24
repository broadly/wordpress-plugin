#Broadly WordPress Plugin
Broadly.com WordPress plugin.

##Usage
Once the plugin has been installed and activated, a new menu item named *"Broadly"* will be available. Click the *"Broadly"* menu item to enter your Broadly ID.

###Shortcode Usage
The shortcode is very simple:

```
[broadly]
```

You can use it on a page or post, or even in a page template. Anywhere you can use a shortcode, basically.

By default, it will pull 6 of the most recent reviews. What if you only want to show 3 reviews?

Below is an example to show only 3 reviews:

```
[broadly embed="reviews" options="recent=3"]
```

The **reviews** embed value can also be changed, but it depends on what services you have with Broadly.