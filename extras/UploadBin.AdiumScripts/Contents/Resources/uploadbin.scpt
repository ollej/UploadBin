FasdUAS 1.101.10   ��   ��    k             l     ��  ��    + % UploadBin - Send file via UploadBin.     � 	 	 J   U p l o a d B i n   -   S e n d   f i l e   v i a   U p l o a d B i n .   
  
 l     ��  ��    $  Copyright 2008 Olle Johansson     �   <   C o p y r i g h t   2 0 0 8   O l l e   J o h a n s s o n      l     ��������  ��  ��        l     ��  ��    2 , Change the following to appropriate values.     �   X   C h a n g e   t h e   f o l l o w i n g   t o   a p p r o p r i a t e   v a l u e s .      j     �� �� 0 username    m        �    t e s t      j    �� �� 
0 passwd    m       �    t e s t     !   j    �� "�� 0 uploadbinurl   " m     # # � $ $ 6 h t t p : / / u p l o a d b i n . n e t / u p l o a d !  % & % j   	 �� '�� 0 
thetimeout 
theTimeout ' m   	 
���� 
 &  ( ) ( j    �� *�� 0 strchoosefile strChooseFile * m     + + � , , 0 C h o o s e   t h e   f i l e   t o   s e n d : )  - . - j    �� /�� 0 strok strOk / m     0 0 � 1 1  O k .  2 3 2 j    �� 4�� &0 struploadbinerror strUploadBinError 4 m     5 5 � 6 6 h T h e r e   w a s   a   p r o b l e m   u p l o a d i n g   t h e   f i l e   t o   U p l o a d B i n . 3  7 8 7 j    �� 9�� $0 strinternalerror strInternalError 9 m     : : � ; ; 4 A n   i n t e r n a l   e r r o r   o c c u r e d : 8  < = < j    �� >�� 0 
strtimeout 
strTimeout > m     ? ? � @ @ * T h e   u p l o a d   t i m e d   o u t . =  A B A j    �� C�� 0 	debugmode   C m    ����  B  D E D l     �� F G��   F &   End of configurable properties.    G � H H @   E n d   o f   c o n f i g u r a b l e   p r o p e r t i e s . E  I J I l     ��������  ��  ��   J  K L K i     # M N M I     ������
�� .aevtoappnull  �   � ****��  ��   N I     �������� 0 
substitute  ��  ��   L  O P O l     ��������  ��  ��   P  Q R Q i   $ ' S T S I      �������� 0 
substitute  ��  ��   T Q     � U V W U k    i X X  Y Z Y l   �� [ \��   [   Get file to upload.    \ � ] ] (   G e t   f i l e   t o   u p l o a d . Z  ^ _ ^ I    �� `���� 0 
selectfile   `  a�� a o    	���� 0 strchoosefile strChooseFile��  ��   _  b c b r     d e d n     f g f 1    ��
�� 
psxp g 1    ��
�� 
rslt e o      ���� 0 filepath   c  h i h l   ��������  ��  ��   i  j k j l   �� l m��   l #  Upload the file to UploadBin    m � n n :   U p l o a d   t h e   f i l e   t o   U p l o a d B i n k  o p o I    �� q���� 0 sendfile   q  r�� r o    ���� 0 filepath  ��  ��   p  s t s r     u v u 1    ��
�� 
rslt v o      ���� 0 downloadurl   t  w x w l   ��������  ��  ��   x  y z y l   �� { |��   { U O Make sure the url starts with http:// otherwise we may have received an error.    | � } } �   M a k e   s u r e   t h e   u r l   s t a r t s   w i t h   h t t p : / /   o t h e r w i s e   w e   m a y   h a v e   r e c e i v e d   a n   e r r o r . z  ~�� ~ Z    i  ��� �  C    " � � � o     ���� 0 downloadurl   � m     ! � � � � �  h t t p : / / � L   % ' � � o   % &���� 0 downloadurl  ��   � k   * i � �  � � � O   * f � � � Z   . e � ��� � � =  . 5 � � � o   . 3���� 0 	debugmode   � m   3 4����  � I  8 O�� � �
�� .sysodlogaskr        TEXT � b   8 A � � � b   8 ? � � � o   8 =���� &0 struploadbinerror strUploadBinError � m   = > � � � � �  
 � o   ? @���� 0 downloadurl   � �� � �
�� 
btns � J   B I � �  ��� � o   B G���� 0 strok strOk��   � �� ���
�� 
dflt � m   J K���� ��  ��   � I  R e�� � �
�� .sysodlogaskr        TEXT � o   R W���� &0 struploadbinerror strUploadBinError � �� � �
�� 
btns � J   X _ � �  ��� � o   X ]���� 0 strok strOk��   � �� ���
�� 
dflt � m   ` a���� ��   � m   * + � ��                                                                                  AdIM   alis    <  Lovelace                   �B'�H+     �	Adium.app                                                       "��i�        ����  	                Applications    �B�      �M�       �  Lovelace:Applications:Adium.app    	 A d i u m . a p p    L o v e l a c e  Applications/Adium.app  / ��   �  ��� � L   g i � � m   g h � � � � �  ��  ��   V R      �� � �
�� .ascrerr ****      � **** � o      ���� 0 errstr errStr � �� ���
�� 
errn � o      ���� 0 errornumber errorNumber��   W k   q � � �  � � � l  q q�� � ���   � D > errorNumber -128 means the user cancelled, just quit quietly.    � � � � |   e r r o r N u m b e r   - 1 2 8   m e a n s   t h e   u s e r   c a n c e l l e d ,   j u s t   q u i t   q u i e t l y . �  � � � Z   q � � � � � � =  q t � � � o   q r���� 0 errornumber errorNumber � m   r s������ � L   w y � � m   w x � � � � �   �  � � � =  | � � � � o   | }���� 0 errornumber errorNumber � m   } �����  �  ��� � I  � ��� � �
�� .sysodlogaskr        TEXT � o   � ����� 0 
strtimeout 
strTimeout � �� � �
�� 
btns � J   � � � �  ��� � o   � ����� 0 strok strOk��   � �� ���
�� 
dflt � m   � ����� ��  ��   � O   � � � � � I  � ��� � �
�� .sysodlogaskr        TEXT � b   � � � � � b   � � � � � b   � � � � � b   � � � � � b   � � � � � o   � ����� $0 strinternalerror strInternalError � m   � � � � � � �  
 � o   � ����� 0 errstr errStr � m   � � � � � � �    ( � o   � ����� 0 errornumber errorNumber � m   � � � � � � �  ) � �� � �
�� 
btns � J   � � � �  ��� � o   � ����� 0 strok strOk��   � �� ���
�� 
dflt � m   � ����� ��   � m   � � � ��                                                                                  AdIM   alis    <  Lovelace                   �B'�H+     �	Adium.app                                                       "��i�        ����  	                Applications    �B�      �M�       �  Lovelace:Applications:Adium.app    	 A d i u m . a p p    L o v e l a c e  Applications/Adium.app  / ��   �  ��� � L   � � � � m   � � � � � � �  ��   R  � � � l     ��������  ��  ��   �  � � � l     �� � ���   �   Post a file to uploadbin    � � � � 2   P o s t   a   f i l e   t o   u p l o a d b i n �  � � � i   ( + � � � I      �� ����� 0 sendfile   �  ��� � o      ���� 0 filepath  ��  ��   � k     7 � �  � � � t     4 � � � k    3 � �  � � � r    + � � � b    ) �  � b    # b    ! b     b     b    	
	 b     b     b     m     � . / u s r / b i n / c u r l   - s   - f   - m   o    ���� 0 
thetimeout 
theTimeout m     � X   - F   a c t i o n = u p l o a d   - F   c l i e n t = r p c   - F   u s e r n a m e = o    ���� 0 username  
 m     �    - F   p a s s w o r d = o    ���� 
0 passwd   m     �    - F   f i l e s = @ o     ���� 0 filepath   m   ! " �     o   # (���� 0 uploadbinurl   � o      ���� 0 uploadscript   �  l  , ,����   C = display dialog uploadscript buttons {strOk} default button 1    � z   d i s p l a y   d i a l o g   u p l o a d s c r i p t   b u t t o n s   { s t r O k }   d e f a u l t   b u t t o n   1  ��  r   , 3!"! l  , 1#��~# I  , 1�}$�|
�} .sysoexecTEXT���     TEXT$ o   , -�{�{ 0 uploadscript  �|  �  �~  " o      �z�z 0 downloadurl  ��   � o     �y�y 0 
thetimeout 
theTimeout � %�x% L   5 7&& o   5 6�w�w 0 downloadurl  �x   � '(' l     �v�u�t�v  �u  �t  ( )*) l     �s+,�s  + !  open file selection dialog   , �-- 6   o p e n   f i l e   s e l e c t i o n   d i a l o g* ./. i   , /010 I      �r2�q�r 0 
selectfile  2 3�p3 o      �o�o 0 promptstring  �p  �q  1 k     44 565 l     �n78�n  7 3 - select a file, save it in variable 'theFile'   8 �99 Z   s e l e c t   a   f i l e ,   s a v e   i t   i n   v a r i a b l e   ' t h e F i l e '6 :;: O     <=< r    >?> l   @�m�l@ I   �k�jA
�k .sysostdfalis    ��� null�j  A �iB�h
�i 
prmpB o    �g�g 0 promptstring  �h  �m  �l  ? o      �f�f 0 thefile theFile= m     CC�                                                                                  AdIM   alis    <  Lovelace                   �B'�H+     �	Adium.app                                                       "��i�        ����  	                Applications    �B�      �M�       �  Lovelace:Applications:Adium.app    	 A d i u m . a p p    L o v e l a c e  Applications/Adium.app  / ��  ; DED l   �eFG�e  F %  return the alias to the caller   G �HH >   r e t u r n   t h e   a l i a s   t o   t h e   c a l l e rE I�dI L    JJ o    �c�c 0 thefile theFile�d  / K�bK l     �a�`�_�a  �`  �_  �b       �^L   #�] + 0 5 : ?�\MNOP�^  L �[�Z�Y�X�W�V�U�T�S�R�Q�P�O�N�[ 0 username  �Z 
0 passwd  �Y 0 uploadbinurl  �X 0 
thetimeout 
theTimeout�W 0 strchoosefile strChooseFile�V 0 strok strOk�U &0 struploadbinerror strUploadBinError�T $0 strinternalerror strInternalError�S 0 
strtimeout 
strTimeout�R 0 	debugmode  
�Q .aevtoappnull  �   � ****�P 0 
substitute  �O 0 sendfile  �N 0 
selectfile  �] 
�\ M �M N�L�KQR�J
�M .aevtoappnull  �   � ****�L  �K  Q  R �I�I 0 
substitute  �J *j+  N �H T�G�FST�E�H 0 
substitute  �G  �F  S �D�C�B�A�D 0 filepath  �C 0 downloadurl  �B 0 errstr errStr�A 0 errornumber errorNumberT �@�?�>�= � � ��<�;�:�9 ��8U�7 ��6 � � � ��@ 0 
selectfile  
�? 
rslt
�> 
psxp�= 0 sendfile  
�< 
btns
�; 
dflt�: 
�9 .sysodlogaskr        TEXT�8 0 errstr errStrU �5�4�3
�5 
errn�4 0 errornumber errorNumber�3  �7���6 �E � k*b  k+  O��,E�O*�k+ O�E�O�� �Y A� 9b  	k  b  �%�%�b  kv�k� 
Y b  �b  kv�k� 
UO�W ]X  ��  �Y H�a   b  �b  kv�k� 
Y *� %b  a %�%a %�%a %�b  kv�k� 
UOa O �2 ��1�0VW�/�2 0 sendfile  �1 �.X�. X  �-�- 0 filepath  �0  V �,�+�*�, 0 filepath  �+ 0 uploadscript  �* 0 downloadurl  W �)
�) .sysoexecTEXT���     TEXT�/ 8b  n�b  %�%b   %�%b  %�%�%�%b  %E�O�j E�oO�P �(1�'�&YZ�%�( 0 
selectfile  �' �$[�$ [  �#�# 0 promptstring  �&  Y �"�!�" 0 promptstring  �! 0 thefile theFileZ C� �
�  
prmp
� .sysostdfalis    ��� null�% � *�l E�UO� ascr  ��ޭ