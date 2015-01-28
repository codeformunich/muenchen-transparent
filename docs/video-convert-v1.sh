avconv -i v1_video.avi -i v1_audio.mp3 -vcodec copy -c:a aac -strict experimental v1.aac.mp4
avconv -i v1_audio.mp3 -i v1_video.avi -codec:v libtheora -codec:a libvorbis v1.ogv

