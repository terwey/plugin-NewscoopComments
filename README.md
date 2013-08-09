in `application/AppKernel.php` add the following:

```
public function registerBundles()
{
    $bundles = array(
        // ...
		new FOS\CommentBundle\FOSCommentBundle(),
		// …
	);
}
```