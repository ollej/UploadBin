FasdUAS 1.101.10   ��   ��    k             l     �� ��    + % UploadBin - Send file via UploadBin.       	  l     �� 
��   
 $  Copyright 2008 Olle Johansson    	     l     ������  ��        l     �� ��    2 , Change the following to appropriate values.         j     �� �� 0 username    m        
 test         j    �� �� 
0 passwd    m       
 test         j    �� �� 0 uploadbinurl    m       % http://www.uploadbin.net/upload         j   	 �� �� 0 
thetimeout 
theTimeout  m   	 
���� 
       j    �� !�� 0 strchoosefile strChooseFile ! m     " "  Choose the file to send:       # $ # j    �� %�� 0 strok strOk % m     & &  Ok    $  ' ( ' j    �� )�� &0 struploadbinerror strUploadBinError ) m     * * : 4There was a problem uploading the file to UploadBin.    (  + , + j    �� -�� $0 strinternalerror strInternalError - m     . .   An internal error occured:    ,  / 0 / j    �� 1�� 0 
strtimeout 
strTimeout 1 m     2 2  The upload timed out.    0  3 4 3 j    �� 5�� 0 	debugmode   5 m    ����  4  6 7 6 l     �� 8��   8 &   End of configurable properties.    7  9 : 9 l     ������  ��   :  ; < ; i     # = > = I     ������
�� .aevtoappnull  �   � ****��  ��   > I     �������� 0 
substitute  ��  ��   <  ? @ ? l     ������  ��   @  A B A i   $ ' C D C I      �������� 0 
substitute  ��  ��   D Q     � E F G E k    i H H  I J I l   �� K��   K   Get file to upload.    J  L M L I    �� N���� 0 
selectfile   N  O�� O o    	���� 0 strchoosefile strChooseFile��  ��   M  P Q P r     R S R n     T U T 1    ��
�� 
psxp U 1    ��
�� 
rslt S o      ���� 0 filepath   Q  V W V l   ������  ��   W  X Y X l   �� Z��   Z #  Upload the file to UploadBin    Y  [ \ [ I    �� ]���� 0 sendfile   ]  ^�� ^ o    ���� 0 filepath  ��  ��   \  _ ` _ r     a b a 1    ��
�� 
rslt b o      ���� 0 downloadurl   `  c d c l   ������  ��   d  e f e l   �� g��   g U O Make sure the url starts with http:// otherwise we may have received an error.    f  h�� h Z    i i j�� k i C    " l m l o     ���� 0 downloadurl   m m     ! n n  http://    j L   % ' o o o   % &���� 0 downloadurl  ��   k k   * i p p  q r q O   * f s t s Z   . e u v�� w u =  . 5 x y x o   . 3���� 0 	debugmode   y m   3 4����  v I  8 O�� z {
�� .sysodlogaskr        TEXT z b   8 A | } | b   8 ? ~  ~ o   8 =���� &0 struploadbinerror strUploadBinError  m   = > � �  
    } o   ? @���� 0 downloadurl   { �� � �
�� 
btns � J   B I � �  ��� � o   B G���� 0 strok strOk��   � �� ���
�� 
dflt � m   J K���� ��  ��   w I  R e�� � �
�� .sysodlogaskr        TEXT � o   R W���� &0 struploadbinerror strUploadBinError � �� � �
�� 
btns � J   X _ � �  ��� � o   X ]���� 0 strok strOk��   � �� ���
�� 
dflt � m   ` a���� ��   t m   * + � ��null     ߀��   	Adium.app�U  � : '�˘�����  ��yp�8 ������ǘ�˘D�xx�����ǘ|�AdIM   alis    <  Lovelace                   �o0�H+     	Adium.app                                                       :&:å}�        ����  	                Applications    �o"�      åo�         Lovelace:Applications:Adium.app    	 A d i u m . a p p    L o v e l a c e  Applications/Adium.app  / ��   r  ��� � L   g i � � m   g h � �      ��  ��   F R      �� � �
�� .ascrerr ****      � **** � o      ���� 0 errstr errStr � �� ���
�� 
errn � o      ���� 0 errornumber errorNumber��   G k   q � � �  � � � l  q q�� ���   � D > errorNumber -128 means the user cancelled, just quit quietly.    �  � � � Z   q � � � � � � =  q t � � � o   q r���� 0 errornumber errorNumber � m   r s������ � L   w y � � m   w x � �       �  � � � =  | � � � � o   | }���� 0 errornumber errorNumber � m   } �����  �  ��� � I  � ��� � �
�� .sysodlogaskr        TEXT � o   � ����� 0 
strtimeout 
strTimeout � �� � �
�� 
btns � J   � � � �  ��� � o   � ����� 0 strok strOk��   � �� ���
�� 
dflt � m   � ����� ��  ��   � O   � � � � � I  � ��� � �
�� .sysodlogaskr        TEXT � b   � � � � � b   � � � � � b   � � � � � b   � � � � � b   � � � � � o   � ����� $0 strinternalerror strInternalError � m   � � � �  
    � o   � ����� 0 errstr errStr � m   � � � �   (    � o   � ����� 0 errornumber errorNumber � m   � � � �  )    � �� � �
�� 
btns � J   � � � �  ��� � o   � ����� 0 strok strOk��   � �� ���
�� 
dflt � m   � ����� ��   � m   � � � �  ��� � L   � � � � m   � � � �      ��   B  � � � l     ������  ��   �  � � � l     �� ���   �   Post a file to uploadbin    �  � � � i   ( + � � � I      �� ����� 0 sendfile   �  ��� � o      ���� 0 filepath  ��  ��   � k     3 � �  � � � t     0 � � � r    / � � � l   - ��� � I   -�� ���
�� .sysoexecTEXT���     TEXT � b    ) � � � b    # � � � b    ! � � � b     � � � b     � � � b     � � � b     � � � b     � � � b     � � � m     � �  curl -s -f -m     � o    ���� 0 
thetimeout 
theTimeout � m     � � 2 , -F action=upload -F client=rpc -F username=    � o    ���� 0 username   � m     � �   -F password=    � o    ���� 
0 passwd   � m     � �   -F files=@    � o     ���� 0 filepath   � m   ! " � �       � o   # (���� 0 uploadbinurl  ��  ��   � o      ���� 0 downloadurl   � o     ���� 0 
thetimeout 
theTimeout �  ��� � L   1 3 � � o   1 2���� 0 downloadurl  ��   �  � � � l     ������  ��   �  � � � l     � ��   � !  open file selection dialog    �  � � � i   , / � � � I      �~ ��}�~ 0 
selectfile   �  ��| � o      �{�{ 0 promptstring  �|  �}   � k      � �    l     �z�z   3 - select a file, save it in variable 'theFile'     O      r     l   	�y	 I   �x�w

�x .sysostdfalis    ��� null�w  
 �v�u
�v 
prmp o    �t�t 0 promptstring  �u  �y   o      �s�s 0 thefile theFile m      �  l   �r�r   %  return the alias to the caller    �q L     o    �p�p 0 thefile theFile�q   � �o l     �n�m�n  �m  �o       �l   �k " & * . 2�j�l   �i�h�g�f�e�d�c�b�a�`�_�^�]�\�i 0 username  �h 
0 passwd  �g 0 uploadbinurl  �f 0 
thetimeout 
theTimeout�e 0 strchoosefile strChooseFile�d 0 strok strOk�c &0 struploadbinerror strUploadBinError�b $0 strinternalerror strInternalError�a 0 
strtimeout 
strTimeout�` 0 	debugmode  
�_ .aevtoappnull  �   � ****�^ 0 
substitute  �] 0 sendfile  �\ 0 
selectfile  �k 
�j  �[ >�Z�Y�X
�[ .aevtoappnull  �   � ****�Z  �Y     �W�W 0 
substitute  �X *j+   �V D�U�T�S�V 0 
substitute  �U  �T   �R�Q�P�O�R 0 filepath  �Q 0 downloadurl  �P 0 errstr errStr�O 0 errornumber errorNumber �N�M�L�K n � ��J�I�H�G ��F�E ��D � � � ��N 0 
selectfile  
�M 
rslt
�L 
psxp�K 0 sendfile  
�J 
btns
�I 
dflt�H 
�G .sysodlogaskr        TEXT�F 0 errstr errStr �C�B�A
�C 
errn�B 0 errornumber errorNumber�A  �E���D �S � k*b  k+  O��,E�O*�k+ O�E�O�� �Y A� 9b  	k  b  �%�%�b  kv�k� 
Y b  �b  kv�k� 
UO�W ]X  ��  �Y H�a   b  �b  kv�k� 
Y *� %b  a %�%a %�%a %�b  kv�k� 
UOa  �@ ��?�>�=�@ 0 sendfile  �? �<�<   �;�; 0 filepath  �>   �:�9�: 0 filepath  �9 0 downloadurl    � � � � ��8
�8 .sysoexecTEXT���     TEXT�= 4b  n�b  %�%b   %�%b  %�%�%�%b  %j E�oO� �7 ��6�5 �4�7 0 
selectfile  �6 �3!�3 !  �2�2 0 promptstring  �5   �1�0�1 0 promptstring  �0 0 thefile theFile   ��/�.
�/ 
prmp
�. .sysostdfalis    ��� null�4 � *�l E�UO�ascr  ��ޭ