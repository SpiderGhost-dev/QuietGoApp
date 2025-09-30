#!/bin/bash
cd /Users/davidbaker/Desktop/QuietGoApp
git add index.php
git commit -m "Fix privacy section: Ensure proper grid-2 column layout

- Privacy section has correct grid grid-2 class structure
- Data Protection and Discreet Design columns properly aligned
- Testimonial 3 updated to Ashley K. (Wellness Enthusiast)
- Resolves live site alignment issues"
git push origin main
echo "Privacy section alignment fix committed and pushed"
