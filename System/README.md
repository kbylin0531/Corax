ע�⣺
   ����Ŀ��֮һд���������͵���ϸע�ͣ����а����ڱ༭�����������ͣ����԰�������ͼ�����
   �ڻ�ȡ�ɱ�����ĵڶ��ַ���
    ** PHP�汾��Ҫ֧�ֵ�5.6 **
       function sum(...$numbers) {
            $acc = 0;
            foreach ($numbers as $n) {
                $acc += $n;
            }
            return $acc;
        }
        echo sum(1, 2, 3, 4);//��� 10
-
        //...������������
        function add($a, $b) {
            return $a + $b;
        }
        echo add(...[1, 2])."\n";//��� 3
        $a = [1, 2];
        echo add(...$a);//��� 3
   ?OB������һ���Ĵ�С���������Ĭ�ϵ�4096�ֽ���ֱ����������������ʱ��ʹ��ob_end_clean();���ܴﵽ���ص�Ч��
   ����Ŀ�������ļ�������ConfigureĿ¼��������Ŀ¼����ʱ������Ƿ����¼���RuntimeĿ¼�µļ��������ļ�
   �����߰�ͼƬת����Base64 ����ַ��http://imgbase64.duoshitong.com/
   ��ģ������ѡ��smarty�ޱ䶯�������Ҫ����smarty�汾����Ŀ¼"System\Extension\smarty"���滻
   ��exit(12)ͬ����ʾΪ��ֹ��������ã���û������ű�ֵ����Ϊ���exit�������β�Ϊ��������,��ô�ʹ���һ���˳���״̬�ţ��˳�״̬�ŵı�׼ȡֵ��Χ�ǣ�0-254֮�䣬����exit(12)Ҳ��ʾ��ֹ��������á����������ݵ�״̬�÷�Ϊ��exit(0-254)����ֹ������ű����÷�Ϊ��exit("��ֹ����")��ѧϰ��죡����
   ���ȡ��̬�������õ�������ʹ��get_called_class,������get_class
   ��
        //����strrposde�õ�����:��ǰ�����Ǵ�0��ʼ�ģ��Ӻ���ǰ��-1��ʼ��
        //        Util::dump(strrpos('bsabab','ab'));//4
        //        Util::dump(strrpos('bsabab','ab',-1));//4
        //        Util::dump(strrpos('bsabab','ab',-2));//4
        //        Util::dump(strrpos('bsabab','ab',-3));//2
        //        Util::dump(strrpos('bsabab','ab',-4));//2
    ��RuntimeĿ¼�´���� Mistight.lite.php�ļ������м����˴����ĺ����࣬���Ա���������ʴ��̵�IO����
       ÿ���޸�Core�ļ���Ҫ�ж��Ƿ���Ҫ����

   11.URL������ThinkPHP�Ĳ����ǣ����ߴ�ģ�鿪ʼ�����������Mist���ǴӲ�����ģ�����
      �ŵ�:URL��ַ���Ժܼ�̣�ֻ��Ҫֱ������������ƾͿ��Է���(�ڵ���ģ�����ŵ�ͻ��)
      ȱ��:���������֮��ķָ����Ҫ�������ã��������ֽ�������

   12.��ά��PHP��Դ��Ŀ��http://sourceforge.net/projects/phpqrcode/?source=top3_dlp_t5
   13.�ļ���Կ�� ��һ���ļ�(ͨ����ͼƬ)��md5ֵΪ���룬ʹ���ļ����е�½��ǰ���ǿ����ļ���½�����������õ��ļ�����ϰ�ߣ�
   14.ConfigureĿ¼�µ�AutoĿ¼�´����ϵͳ�Զ����ɵ�����
   15.stdClass���ö���
      $condition = new stdClass();
      $condition->name = 'thinkphp';
   16.PHP��ɫ�����ƺ����Ĳ��������Խṹ�������ں�����ʱЧ�ʷǳ���
   17. �������Ը�ֵ
        ʾ�����룺
   			foreach (array('csrf_expire', 'csrf_token_name', 'csrf_cookie_name') as $key)
   			{
   				if (NULL !== ($val = config_item($key)))
   				{
   					$this->{'_'.$key} = $val;
   				}
   			}
   18.ctype_digit ��� text �ַ�����һ��ʮ�������֣��ͷ��� TRUE  ����֮�ͷ��� FALSE
   19.������ر�������form��Ĭ���ύ�ķ�����get
   20.����php���ڷ��������ִ�Сд�����Զ���
            <h1><a href="{U url='admin/member/index/indexmain' }">MatAdmin</a></h1>
            <h1><a href="{U url='admin/member/index/indexMain' }">MatAdmin</a></h1>
        ������ͬ����URLЧ��

   21.Router.class.php�е� 379 ��ע�͵���
        $_GET��$_REQUEST����ͬ��������̬���Ԫ�ص�$_GET�к�$_REQUEST�в����Զ���ӣ���Ҫ������Ȼ����Ϊ$_REQUESTÿʱÿ�̶���
        $_GET��$_POST�Ĳ����ϣ�����ֻ�Ǳ���HTTP����������ʱ���״̬
����Ӣ�Ķ��գ�
divider - �ָ���
  22.������Ϊ�����еģ����δ��ָ����action���ݲ�����Ĭ��Ϊnull������thinkphp���쳣��ʾ
  23.
     ������http://localhost/MinShuttler/Publicʱ
       REQUEST_URI : /MinShuttler/Public/
       SCRIPT_NAME : /MinShuttler/Public/index.php