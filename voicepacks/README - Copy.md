# roborock-voicepack-custom
Create pkg file of your custom wav files for easy upload to valetudo

place all the wav files inside the pkg folder of waw2pkg and run the bat file.
A pkg file will be created inside, which you can then generate a hash, powershell/terminal example:
 Get-FileHash .\glados_custom_v1.pkg -Algorithm MD5
and copy the HASH
Now you have the pkg file, put on IIS default folder (or some other method) to have the direct url to pkg, enter the HASH and language code (i used PL for the fun of it)
and valetudo will upload and activate voicepack.

Tried on S5, and 1st gen Roborock, understand S5 MAX have some intricacies.
