//
//  MovieTableViewCell_iPad.h
//  Borhan
//
//  Created by Pavel on 14.03.12.
//  Copyright (c) 2012 Borhan. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface MovieTableViewCell_iPad : UITableViewCell {

    
    IBOutlet UIView *cell1View;
    IBOutlet BorhanThumbView *cell1Image;
    IBOutlet UILabel *cell1Label1;
    IBOutlet UILabel *cell1Label2;
    
    IBOutlet UIView *cell2View;
    IBOutlet BorhanThumbView *cell2Image;
    IBOutlet UILabel *cell2Label1;
    IBOutlet UILabel *cell2Label2;
    
    IBOutlet UIView *cell3View;
    IBOutlet BorhanThumbView *cell3Image;
    IBOutlet UILabel *cell3Label1;
    IBOutlet UILabel *cell3Label2;
    
    IBOutlet UIView *cell4View;
    IBOutlet BorhanThumbView *cell4Image;
    IBOutlet UILabel *cell4Label1;
    IBOutlet UILabel *cell4Label2;
    
    int index;
    
    UIViewController *parentController;
    
}

- (void)updateCell1:(BorhanMediaEntry *)mediaEntry;
- (void)updateCell2:(BorhanMediaEntry *)mediaEntry;
- (void)updateCell3:(BorhanMediaEntry *)mediaEntry;
- (void)updateCell4:(BorhanMediaEntry *)mediaEntry;

- (IBAction)selectCellView:(UIButton *)button;

@property (nonatomic, retain) IBOutlet UIView *cell1View;
@property (nonatomic, retain) IBOutlet UIView *cell2View;
@property (nonatomic, retain) IBOutlet UIView *cell3View;
@property (nonatomic, retain) IBOutlet UIView *cell4View;
@property int index;
@property (nonatomic, retain) UIViewController *parentController;

@end
