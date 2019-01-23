# Require any additional compass plugins here.

# Set this to the root of your project when deployed:
http_path = "/"
project_path = File.dirname(__FILE__) + '/../'
css_dir = "css"
sass_dir = "compass"
images_dir = "img"
http_images_path = "img"
generated_images_dir = "img"
http_generated_images_path = "img";
http_stylesheets_path = "css"
javascripts_dir = "js"

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed
output_style = :compressed

# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
line_comments = false

on_stylesheet_saved do |filename|
	if filename.end_with?(".css") and File.exists?(filename)
		Zlib::GzipWriter.open(filename + '.gz') do |gz|
			gz.mtime = File.mtime(filename)
			gz.orig_name = filename
			gz.write IO.read(filename)
			gz.close
		end
	end
end
